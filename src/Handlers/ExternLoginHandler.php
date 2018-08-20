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
        $service = $request->getParam('service');
        $language = $request->getParam('lang', 'en');

        // TODO: check referer and verify, that the callback points to the same domain!
        //       this should be there in case of some configuration error

        if (!$callback)
            $pageContents = $this->renderError("No callback URL specified");
        else if (!$service)
            $pageContents = $this->renderError("No service specified");
        else
            $pageContents = $this->renderLogin($request->getUri()->getBasePath(), $service, $language, $callback);

        return $response->withStatus(200)->withBody($pageContents);
    }

    public function handleFormLoginRequest(Request $request, Response $response, ParameterContainer $args)
    {
        $username = $request->getParam('username');
        $password = $request->getParam('password');

        $callback = $request->getParam('callback');
        $service = $request->getParam('service');
        $language = $request->getParam('lang', 'en');

        $usr = $this->users()->getUserByUsername($username);
        if (!$usr)
            $rc = ReturnCode::FAIL_AUTH_FAILED;
        else
            $rc = $this->auth()->validatePasswordAuth($usr['id'], $password, $auth_id, $services);

        if ($rc !== ReturnCode::OK)
        {
            $pageContents = null;

            // bad username/password, expired or disabled auth info
            if ($rc === ReturnCode::FAIL_AUTH_FAILED || $rc === ReturnCode::FAIL_AUTH_EXPIRED || $rc === ReturnCode::FAIL_AUTH_DISABLED)
                $pageContents = $this->renderLogin($request->getUri()->getBasePath(), $service, $language, $callback, "auth_err_".$rc);
            // other error code is considered generic auth fail
            else
                $pageContents = $this->renderLogin($request->getUri()->getBasePath(), $service, $language, $callback, "General error");

            return $response->withStatus(200)->withBody($pageContents);
        }

        $token = $this->createToken($usr['id'], $auth_id, [ $service ]);

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
     * @param string $error
     * @return \Slim\Http\Body
     */
    private function renderLogin($baseUrl, $service, $language, $callback, $error = '')
    {
        $rnd = new SimpleRenderer("Login", $language);
        return $rnd->renderStream([
            'BASE_URL' => $baseUrl,
            'SERVICE' => $service,
            'SERVICE_ENCODED' => urlencode($service),
            'LANGUAGE' => $language,
            'LANGUAGE_ENCODED' => urlencode($language),
            'CALLBACK_URL' => $callback,
            'CALLBACK_URL_ENCODED' => urlencode($callback),
            'ERROR_STR' => new TranslationWrapper($error)
        ]);
    }
}
