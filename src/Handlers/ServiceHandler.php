<?php

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Handler for all service-related routines, which should be maintained through API
 */
class ServiceHandler extends BaseHandler
{
    public function handleGetData(Request $request, Response $response, ParameterContainer $args)
    {
        $username = $request->getParam('username');
        $auth_token = $request->getParam('auth_token');
        $service = $request->getParam('service');
        $secret = $request->getParam('service_secret');
        if (!$service || !$secret || (!$username && !$auth_token))
            return $response->withStatus(400);

        if ($username)
            $usr = $this->users()->getUserByUsername($username);
        else if ($auth_token)
        {
            $tkinfo = $this->auth()->getTokenInfo($auth_token);
            if ($tkinfo->valid)
                $usr = $this->users()->getUserById($tkinfo->users_id);
            else
                return $response->withStatus(401);
        }

        if (!$usr)
            return $response->withStatus(404);

        if (!$this->services()->validateServiceSecret($service, $secret))
            return $response->withStatus(403);
        
        $svc = $this->services()->getServiceByName($service);

        $data = $this->services()->getUserServiceData($usr['id'], $svc['id']);

        return $response->withStatus(200)->withJson([
            'services' => [
                $service => $data
            ]
        ]);
    }

    public function handleSetData(Request $request, Response $response, ParameterContainer $args)
    {
        $username = $request->getParam('username');
        $auth_token = $request->getParam('auth_token');
        $service = $request->getParam('service');
        $secret = $request->getParam('service_secret');
        $data = $request->getParam('data');
        if (!$service || !$secret || (!$username && !$auth_token) || !$data)
            return $response->withStatus(400);

        if ($username)
            $usr = $this->users()->getUserByUsername($username);
        else if ($auth_token)
        {
            $tkinfo = $this->auth()->getTokenInfo($auth_token);
            if ($tkinfo->valid)
                $usr = $this->users()->getUserById($tkinfo->users_id);
            else
                $response->withStatus(401);
        }

        if (!$usr)
            return $response->withStatus(404);

        if (!$this->services()->validateServiceSecret($service, $secret))
            return $response->withStatus(403);

        $svc = $this->services()->getServiceByName($service);

        $decoded = json_decode($data);
        if (!$decoded)
            return $response->withStatus(400);

        $res = $this->services()->setUserServiceData($usr['id'], $svc['id'], $decoded);
        if (!$res)
            return $response->withStatus(409);

        return $response->withStatus(200);
    }

    public function handleMediateService(Request $request, Response $response, ParameterContainer $args)
    {
        $username = $request->getParam('username');
        $auth_token = $request->getParam('auth_token');
        $service = $request->getParam('service');
        $secret = $request->getParam('service_secret');
        $service_to_mediate = $request->getParam('service_to_mediate');
        if (!$service || !$service_to_mediate || !$secret || (!$username && !$auth_token))
            return $response->withStatus(400);

        if ($username)
            $usr = $this->users()->getUserByUsername($username);
        else if ($auth_token)
        {
            $tkinfo = $this->auth()->getTokenInfo($auth_token);
            if ($tkinfo->valid)
                $usr = $this->users()->getUserById($tkinfo->users_id);
            else
                return $response->withStatus(401);
        }

        if (!$usr)
            return $response->withStatus(404);

        if (!$this->services()->validateServiceSecret($service, $secret))
            return $response->withStatus(403);

        $parent_id = $this->services()->getServiceByName($service);
        $mediated_id = $this->services()->getServiceByName($service_to_mediate);

        if (!$parent_id || !$mediated_id)
            return $response->withStatus(404);

        if (!$this->services()->isMediatedByService($parent_id, $mediated_id))
            return $response->withStatus(406);

        $token = $this->createToken($usr['id'], $service_to_mediate);

        return $response->withStatus(200)->withJson([
            'auth_token' => $token->token,
        ]);
    }
}
