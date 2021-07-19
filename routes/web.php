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

$router->group(['prefix' => 'auth'], function () {
    //$router->get('home', 'UserController@show');
});

$router->group(['prefix' => 'api'], function () use ($router){
    $router->get('home', 'HomeController@show');
    $router->get('config', 'HomeController@config');
    $router->get('vehiculos', 'VehiculosController@find');
    $router->get('vehiculo/{slug}', 'VehiculosController@detalle');
    $router->get('servicios', 'OtrosController@getServicios');
    $router->get('concesionarios', 'OtrosController@concesionarios');
    $router->get('comunidad', 'ComunidadController@show');
});