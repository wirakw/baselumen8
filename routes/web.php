<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('/email/verify', function ()  {
    return view('verification');
});
$router->post('/email/request-verification', ['as' => 'email.request.verification', 'uses' => 'Api\AuthController@emailRequestVerification']);

$router->post('/email/verify', ['as' => 'email.verify', 'uses' => 'Api\AuthController@emailVerify']);

$router->group(['prefix' => 'api/v1'], function () use ($router) {
    // auth
    $router->post('register', 'Api\AuthController@register');
    $router->post('login', 'Api\AuthController@login');
    // user
    $router->get('profile', 'Api\UserController@profile');
    $router->put('user/{id}', 'Api\UserController@update');
    $router->post('user/deviceToken', 'Api\UserController@setDeviceToken');
    $router->post('updatePhoto', 'Api\UserController@updatePhoto');
});
