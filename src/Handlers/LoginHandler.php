<?php

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Handler for all login and validation routines
 */
class LoginHandler extends BaseHandler
{
    /**
     * Return codes:
     *      200 - OK
     *      400 - request body does not contain everything it should
     *      401 - bad username or password
     *      403 - caller is not a valid service provider
     *      405 - requested service array contains at least one, that is not active for given user
     *      406 - given auth string (password, ..) is disabled for this user
     *      409 - service provider IP exceeded maximum number of attempts
     *      410 - validity of given auth string (password, ..) expired
     *      412 - attempts for given username exceeded the limit
     */
    public function handleLogin(Request $request, Response $response, ParameterContainer $args)
    {
        $username = $args->get('username');
        $auth_type = $args->get('auth_type');
        $auth_string = $args->get('auth_string');
        $serviceName = $args->get('service');
        $service_provider_str = $args->get('service_provider_name');
        $service_provider_secret = $args->get('service_provider_secret');
        if ($username === null || $auth_type === null || $auth_string === null || $service_provider_str === null || $service_provider_secret === null)
            return $response->withStatus(400);

        $rsvc_record = $this->services()->getServiceByName($rsvc);
        if (!$rsvc_record)
            return $response->withStatus(403);

        // direct = individual fail count (per IP)
        $individualFailCount = ($rsvc_record['provided_type'] === ServiceProvidedType::DIRECT);

        $remoteIP = $this->getRemoteIP();

        if ($this->guard()->getFailCountForServiceProviderIP($remoteIP) >= $this->guard()->getMaxServiceProviderIPAttempts())
            return $response->withStatus(409);

        if (!$this->services()->validateServiceSecret($service_provider_str, $service_provider_secret))
        {
            $this->guard()->accumulateFailForServiceProviderIP($remoteIP);
            return $response->withStatus(403);
        }

        $usr = $this->users()->getUserByUsername($username);
        if (!$usr)
            return $response->withStatus(401);

        if ($this->guard()->getFailCountForUsername($username) >= $this->guard()->getMaxUsernameAttempts())
            return $response->withStatus(412);

        $rc = ReturnCode::FAIL_AUTH_FAILED;
        $auth_id = 0;
        $services = [];

        if ($auth_type === AuthType::PASSWORD)
            $rc = $this->auth()->validatePasswordAuth($usr['id'], $auth_string, $auth_id, $services);
        else if ($auth_type === AuthType::PUBKEY)
        {
            // TODO
            // pubkey authentication should have slightly different flow
        }

        // bad username or auth info
        if ($rc === ReturnCode::FAIL_AUTH_FAILED)
        {
            if ($individualFailCount)
                $this->guard()->accumulateFailForIP($remoteIP);
            else
                $this->guard()->accumulateFailForServiceProviderIP($remoteIP);

            $this->guard()->accumulateFailForUsername($username);

            return $response->withStatus(401);
        }
        // auth info expired (due to validity period specified by user)
        else if ($rc === ReturnCode::FAIL_AUTH_EXPIRED)
            return $response->withStatus(410);
        // auth info is disabled (by user or by admin)
        else if ($rc === ReturnCode::FAIL_AUTH_DISABLED)
            return $response->withStatus(406);
        // other error code is considered generic auth fail
        else if ($rc !== ReturnCode::OK)
            return $response->withStatus(401);

        // is serviceName in already subscribed services list? no = try subscribe
        if (!in_array($serviceName, $services))
        {
            // service is not open, therefore we cannot subscribe
            if ($rsvc_record['subscribe_type'] !== ServiceSubscriptionType::OPEN)
                return $response->withStatus(405);
            else
            {
                // now we can be sure the service exists an has "open" subscription type

                if (!$this->auth()->subscribeAuthToService($auth_id, $serviceName))
                    return $response->withStatus(405);
            }
        }

        // now everything should be valid, let us create token, store it to database and send it to user

        $token = $this->createToken($usr['id'], $auth_id, $services);

        return $response->withStatus(200)->withJson([
            'auth_token' => $token->token,
        ]);
    }

    /**
     * Return codes:
     *      200 - OK
     *      400 - request body does not contain everything it should
     *      401 - token is not valid for given (sub)set of services
     *      404 - requested service not found
     *      409 - service secret does not match
     *      410 - token is not valid (expired, or never was)
     */
    public function handleValidateToken(Request $request, Response $response, ParameterContainer $args)
    {
        $token = $args->get('token');
        $services_requested = $args->get('services');
        $services_secrets = $args->get('secrets');
        if ($token === null || $services_requested === null || $services_secrets === null || count($services_requested) !== count($services_secrets))
            return $response->withStatus(400);

        // we store service records for later
        $services = [];

        // check each service - if it exists and if it supplies valid secret
        foreach ($services_requested as $i => $srv)
        {
            $serviceRecord = $this->services()->getServiceByName($srv);
            if (!$serviceRecord)
                return $response->withStatus(404);
            // case-sensitive comparison
            if (strcmp($serviceRecord['secret'], $services_secrets[$i]) !== 0)
                return $response->withStatus(409);

            $services[$i] = $serviceRecord;
        }

        // look-up token info; if the token is not valid, or is not created for requested services, abort
        $info = $this->auth()->getTokenInfo($token);
        if (!$info->valid)
            return $response->withStatus(410);
        if (count(array_intersect($info->services, $services_requested)) !== count($services_requested))
            return $response->withStatus(401);

        $usr = $this->users()->getUserById($info->users_id);
        // this should not happen, because database constraints and foreign keys and stuff
        if (!$usr)
            return $response->withStatus(401);

        $serviceData = [];
        foreach ($services as $srv)
            $serviceData[$srv['name']] = $this->services()->getUserServiceData($usr['id'], $srv['id']);

        // we don't want to expose user ID, because that's our internal information
        // rather, services should identify user by his unique username or email
        unset($usr['id']);

        return $response->withStatus(200)->withJson([
            'user' => $usr,
            'services' => $serviceData
        ]);
    }
}
