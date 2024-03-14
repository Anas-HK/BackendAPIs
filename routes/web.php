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
    // Route for requesting a password reset email
    $router->post('/forgot-password', 'ForgotPasswordController@forgotPassword');
    // Route for resetting the password using the provided token
    $router->post('/reset-password', 'ForgotPasswordController@resetPassword');
    $router->post('/verify-otp', 'AuthController@verifyOtp');
    $router->post('/register/consumer', 'AuthController@registerConsumer');
    $router->post('/register/business', 'AuthController@registerBusiness');
    $router->post('/login', 'AuthController@login');

    // Subscription routes
    $router->get('subscription/{id}', 'SubscriptionController@get');
    $router->get('subscription', 'SubscriptionController@getAll');
    $router->get('subscription/search/{keyword}', 'SubscriptionController@search');
    $router->post('subscription', 'SubscriptionController@insert');
    $router->put('subscription/{id}', 'SubscriptionController@update');
    $router->delete('subscription/{id}', 'SubscriptionController@delete');

    // Route requsting business categories
    $router->get('/categories', 'BusinessCategories@getAll');

    // Routes relating to profile setup
    // $router->get('/profile-setup', 'ProfileSetup@BusinessId');

    // Route for data insertion after profile setup
    $router->post('/insert-data', 'DataInsertionController@insertData');

    // We will put our posts route in a group which will check auth middleware so that user will have to
    // log in to access post CRUD operations.
    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->post('/logout', 'AuthController@logout');
        $router->get('/posts', 'PostController@index');
        $router->post('/posts', 'PostController@store');
        $router->put('/posts/{id}', 'PostController@update');
        $router->delete('/posts/{id}', 'PostController@delete');
    });

    // Taking apis outside for testing



    
});
