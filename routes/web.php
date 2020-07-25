<?php

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

$router->group(['middleware' => 'controller'], function ($app) {
    # Authentication
    $app->post('auth/login', ['uses' => 'AuthController@login']);
});

$router->group(['middleware' => ['auth:api', 'controller']], function ($app) {
    # Authentication
    $app->get('auth/refresh', ['uses' => 'AuthController@refresh']);

    # Assets
    $app->get('v1/asset', ['uses' => 'AssetController@index']);
    $app->post('v1/asset', ['uses' => 'AssetController@store']);
    $app->put('v1/asset/{id}', ['uses' => 'AssetController@update']);
    $app->delete('v1/asset/{id}', ['uses' => 'AssetController@destroy']);
});
