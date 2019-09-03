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
        $service = $request->getParam('service');
        $secret = $request->getParam('secret');
        if (!$service || !$secret || !$username)
            return $response->withStatus(400);

        $usr = $this->users()->getUserByUsername($username);
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
        $service = $request->getParam('service');
        $secret = $request->getParam('secret');
        $data = $request->getParam('data');
        if (!$service || !$secret || !$username || !$data)
            return $response->withStatus(400);

        $usr = $this->users()->getUserByUsername($username);
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
}
