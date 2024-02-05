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
    // return $router->app->version();
    echo "Hello Lumen";
});

$router->group(['prefix'=>'api'], function() use ($router) {
    $router->post('/register', 'AuthController@register');
    $router->post('/login', 'AuthController@login');

    // We will put our posts route in a group which will check auth middleware so that user will have to
    // login to access post CRUD operations.
    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->post('/api/logout', 'AuthController@logout');
        $router->get('/posts', 'PostController@index');
        $router->post('/posts', 'PostController@store');
        $router->put('/posts/{id}', 'PostController@update');
        $router->delete('/posts/{id}', 'PostController@delete');
    });

    // Taking apis outside for testing


});
