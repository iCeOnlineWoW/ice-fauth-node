<?php

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Handler for all login and validation routines
 */
class LoginHandler extends BaseHandler
{
    public function handleLogin(Request $request, Response $response, ParameterContainer $args)
    {
        $username = $args->get('username');
        $auth_type = $args->get('auth_type');
        $auth_string = $args->get('auth_string');
        $services_requested = $args->get('services');
        if ($username === null || $auth_type === null || $auth_string === null)
            return $response->withStatus(400);

        $usr = $this->users()->getUserByUsername($username);
        if (!$usr)
            return $response->withStatus(401);

        $rc = ReturnCode::FAIL_AUTH_FAILED;
        $auth_id = 0;
        $services = [];

        if ($auth_type === AuthType::PASSWORD)
            $rc = $this->auth()->validatePasswordAuth($usr['id'], $auth_string, $auth_id, $services);
        else if ($auth_type === AuthType::PUBKEY)
        {
            // TODO
        }

        // bad username or auth info
        if ($rc === ReturnCode::FAIL_AUTH_FAILED)
            return $response->withStatus(401);
        // auth info expired (due to validity period specified by user)
        else if ($rc === ReturnCode::FAIL_AUTH_EXPIRED)
            return $response->withStatus(410);
        // auth info is disabled (by user or by admin)
        else if ($rc === ReturnCode::FAIL_AUTH_DISABLED)
            return $response->withStatus(406);
        // other error code is considered generic auth fail
        else if ($rc !== ReturnCode::OK)
            return $response->withStatus(401);

        // login request is for a subset of services
        if ($services_requested)
        {
            // the auth info has to be valid for all of requested services
            if (count(array_intersect($services_requested, $services)) === count($services_requested))
                $services = $services_requested;
            // otherwise it's not valid
            else
                return $response->withStatus(405);
        }

        // now everything should be valid, let us create token, store it to database and send it to user

        // at first, see if there's a token for those services - if yes, invalidate it
        $existing = $this->auth()->getTokenForServices($usr['id'], $services);
        if ($existing->valid)
            $this->auth()->removeToken($existing->id);

        $token = $this->auth()->generateToken($auth_id);

        return $response->withStatus(200)->withJson([
            'auth_token' => $token->token,
        ]);
    }

    public function handleValidateToken(Request $request, Response $response, ParameterContainer $args)
    {
        $token = $args->get('token');
        $services_requested = $args->get('services');
        if ($token === null || $services_requested === null)
            return $response->withStatus(400);

        // look-up token info; if the token is not valid, or is not created for requested services, abort
        $info = $this->auth()->getTokenInfo($token);
        if (!$info->valid)
            return $response->withStatus(410);
        if (count(array_intersect($info->services, $services_requested)) !== count($services_requested))
            return $response->withStatus(401);

        return $response->withStatus(200);
    }
}
