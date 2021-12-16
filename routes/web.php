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

$router->group(['prefix' => 'api/auth/'], function() use ($router) {
    $router->post('login', 'AuthController@postLogin');
    $router->post('register', 'AuthController@create');
});
$router->group(['prefix' => 'api/'], function() use ($router) {
    $router->get('user_details', 'UserDetailsController@getUserDetails');
    $router->put('user_details', 'UserDetailsController@store');
    $router->post('user_details', 'UserDetailsController@edit');
    $router->delete('user_details', 'UserDetailsController@delete');
    $router->get('user_details/telegram', 'UserDetailsController@storeTelegramUserID');
    $router->get('user_details/telegram/check', 'UserDetailsController@checkCode');
});
//Subscription
$router->group(['prefix' => 'api/subscription/'], function() use ($router) {
    $router->get('history', 'SubscriptionController@getSubscriptionHistory');
    $router->post('add', 'SubscriptionController@addSub');


});
//user urls and checking
$router->group(['prefix' => 'api/url/'], function() use ($router) {
    $router->get('all', 'UrlController@getAllUserUrls');
    $router->post('add', 'UrlController@addUrl');
    $router->get('state/{id}', 'UrlController@changeStateOfURL');

});








