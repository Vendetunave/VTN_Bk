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

$router->group(['prefix' => 'auth'], function () use ($router) {
    $router->post('login', 'AuthController@login');
    $router->get('me', 'AuthController@me');
    $router->get('profile/{id}', 'UsuarioController@profile');
    $router->get('publicaciones/{id}', 'UsuarioController@publicaciones');
    $router->get('favoritos/{id}', 'UsuarioController@favoritos');
    $router->get('busquedas/{id}', 'UsuarioController@busquedas');
});
//Public routes
$router->group(['prefix' => 'api'], function () use ($router){
    $router->get('home', 'HomeController@show');
    $router->get('config', 'HomeController@config');
    $router->get('vehiculos', 'VehiculosController@find');
    $router->get('fichas_tecnicas', 'VehiculosController@fichas_tecnicas');
    $router->get('accesorios', 'VehiculosController@accesorios');
    $router->get('vehiculo/{slug}', 'VehiculosController@detalle');
    $router->get('servicios', 'OtrosController@getServicios');
    $router->get('concesionarios', 'OtrosController@concesionarios');
    $router->get('comunidad', 'ComunidadController@show');
});