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

$router->group(['prefix' => 'api/v1'], function () use ($router) {
    $router->get('objects',  [
        'middleware' => 'security',
        'uses' => 'ObjectController@list'
    ]);
    $router->get('objects/{key}',  [
        'middleware' => 'security',
        'uses' => 'ObjectController@get'
    ]);
    $router->post('objects', ['uses' => 'ObjectController@post']);
});