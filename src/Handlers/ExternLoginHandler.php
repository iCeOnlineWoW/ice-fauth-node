<?php

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Handler for all external login and validation routines
 */
class ExternLoginHandler extends BaseHandler
{
    public function handleExternalLoginRequest(Request $request, Response $response, ParameterContainer $args)
    {
        // separate GET arguments: callback (URL), service (string), language (cs, en, ...)
        $callback = $request->getParam('callback');
        $serviceName = $request->getParam('service');
        $language = $request->getParam('lang', 'en');

        $service = $this->services()->getServiceByName($serviceName);

        // TODO: check referer and verify, that the callback points to the same domain!
        //       this should be there in case of some configuration error

        if (!$callback)
            $pageContents = $this->renderError("No callback URL specified");
        else if (!$serviceName || !$service)
            $pageContents = $this->renderError("No service specified");
        else
            $pageContents = $this->renderLogin($request->getUri()->getBasePath(), $serviceName, $language, $callback, $service['title']);

        return $response->withStatus(200)->withBody($pageContents);
    }

    public function handleFormLoginRequest(Request $request, Response $response, ParameterContainer $args)
    {
        $username = $request->getParam('username');
        $password = $request->getParam('password');

        $callback = $request->getParam('callback');
        $serviceName = $request->getParam('service');
        $language = $request->getParam('lang', 'en');

        $service = $this->services()->getServiceByName($serviceName);

        $auth_id = -1;
        $remoteIP = $this->getRemoteIP();

        if ($this->guard()->getFailCountForIP($remoteIP) >= $this->guard()->getMaxIPAttempts())
            $rc = ReturnCode::FAIL_ATTEMPTS_IP;
        else
        {
            $usr = $this->users()->getUserByUsername($username);
            if (!$usr)
                $rc = ReturnCode::FAIL_AUTH_FAILED;
            else
            {
                if ($this->guard()->getFailCountForUsername($username) >= $this->guard()->getMaxUsernameAttempts())
                    $rc = ReturnCode::FAIL_ATTEMPTS_USERNAME;
                else
                    $rc = $this->auth()->validatePasswordAuth($usr['id'], $password, $auth_id, $services);
            }
        }

        if ($rc === ReturnCode::OK)
        {
            // is serviceName in already subscribed services list? no = try subscribe
            if (!in_array($serviceName, $services))
            {
                if (!$service || $service['subscribe_type'] !== ServiceSubscriptionType::OPEN)
                    $rc = ReturnCode::FAIL_UNAUTH_SERVICE;

                // now we can be sure the service exists an has "open" subscription type

                if (!$this->auth()->subscribeAuthToService($auth_id, $serviceName))
                    $rc = ReturnCode::FAIL_UNAUTH_SERVICE;
            }
        }

        if ($rc !== ReturnCode::OK)
        {
            $pageContents = null;

            // accumulate attempt count if not already exceeded the limit
            if ($rc !== ReturnCode::FAIL_ATTEMPTS_USERNAME && $rc !== ReturnCode::FAIL_ATTEMPTS_IP)
            {
                // accumulate username count only if user with such username exists
                if ($usr)
                    $this->guard()->accumulateFailForUsername($username);
                $this->guard()->accumulateFailForIP($remoteIP);
            }

            // bad username/password, expired or disabled auth info
            if ($rc === ReturnCode::FAIL_AUTH_FAILED || $rc === ReturnCode::FAIL_AUTH_EXPIRED || $rc === ReturnCode::FAIL_AUTH_DISABLED
                    || $rc === ReturnCode::FAIL_ATTEMPTS_USERNAME || $rc === ReturnCode::FAIL_ATTEMPTS_IP || $rc === ReturnCode::FAIL_UNAUTH_SERVICE)
                $pageContents = $this->renderLogin($request->getUri()->getBasePath(), $serviceName, $language, $callback, $service['title'], "auth_err_".$rc);
            // other error code is considered generic auth fail
            else
                $pageContents = $this->renderLogin($request->getUri()->getBasePath(), $serviceName, $language, $callback, $service['title'], "General error");

            return $response->withStatus(200)->withBody($pageContents);
        }

        $token = $this->createToken($usr['id'], $auth_id, [ $serviceName ]);

        $callback .= (strrpos($callback, '?') > 0) ? '&' : '?';
        $callback .= 'token='.urlencode($token->token);

        return $response->withRedirect($callback);
    }

    /**
     * Render error page
     * @param string $errorString
     * @return \Slim\Http\Body
     */
    private function renderError($errorString)
    {
        $rnd = new SimpleRenderer("LoginSettingError");
        return $rnd->renderStream([
            'ERROR_STR' => $errorString
        ]);
    }

    /**
     * Render login page (optionally with auth error)
     * @param string $baseUrl
     * @param string $service
     * @param string $language
     * @param string $callback
     * @param string $serviceTitle
     * @param string $error
     * @return \Slim\Http\Body
     */
    private function renderLogin($baseUrl, $service, $language, $callback, $serviceTitle, $error = '')
    {
        $rnd = new SimpleRenderer("Login", $language);
        return $rnd->renderStream([
            'BASE_URL' => $baseUrl,
            'SERVICE' => $service,
            'SERVICE_TITLE' => $serviceTitle,
            'SERVICE_ENCODED' => urlencode($service),
            'LANGUAGE' => $language,
            'LANGUAGE_ENCODED' => urlencode($language),
            'CALLBACK_URL' => $callback,
            'CALLBACK_URL_ENCODED' => urlencode($callback),
            'ERROR_STR' => new TranslationWrapper($error)
        ]);
    }
}
