<?php

use Slim\Http\Request;
use Slim\Http\Response;

// map of requests to be mapped using $app instance later
// only this array should be modified in this file
$REQUEST_MAP = [
    'LoginHandler' => [
        [ '/login', 'POST', 'handleLogin' ],
        [ '/validate-token', 'POST', 'handleValidateToken' ],
    ],
    'ExternLoginHandler' => [
        [ '/login-request', 'GET', 'handleExternalLoginRequest' ],
        [ '/login-request', 'POST', 'handleFormLoginRequest' ],
    ],
];

// map each request to given handler and function
foreach ($REQUEST_MAP as $handler => $requests)
{
    foreach ($requests as $req)
    {
        list($path, $method, $handleFunc) = $req;
        call_user_func([ $app, strtolower($method) ], $path, function(Request $request, Response $response, array $args) use ($handler, $handleFunc) {
            $handlerInstance = new $handler;
            $handlerInstance->startup($this);

            $paramContainer = new ParameterContainer(array_merge($args, (array)json_decode($request->getBody())));

            return $handlerInstance->{$handleFunc}($request, $response, $paramContainer);
        });
    }
}

$container = $app->getContainer();

// method not allowed - do not bother with exhaustive info, just send code
$container['notAllowedHandler'] = function($container) {
    return function ($request, $response, $methods) use ($container) {
        return $response->withStatus(405)
            ->withHeader('Allow', implode(', ', $methods))
            ->withHeader('Content-type', 'text/html')
            ->write('');
    };
};

// page not found - again, just send 404 code
$container['notFoundHandler'] = function($container) {
    return function ($request, $response) use ($container) {
        return $response->withStatus(404)
            ->withHeader('Content-type', 'text/html')
            ->write('');
    };
};
