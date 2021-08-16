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
    $router->post('register', 'AuthController@register');

    $router->get('profile/{id}', 'UsuarioController@profile');
    $router->get('publicaciones/{id}', 'UsuarioController@publicaciones');
    $router->get('favoritos/{id}', 'UsuarioController@favoritos');
    $router->get('busquedas/{id}', 'UsuarioController@busquedas');
    $router->get('form_producto', 'UsuarioController@form_producto');
    
});
//Public routes
$router->group(['prefix' => 'api'], function () use ($router){
    $router->get('home', 'HomeController@show');
    $router->get('config', 'HomeController@config');
    $router->get('vehiculos', 'VehiculosController@find');
    $router->get('vehiculo/{slug}', 'VehiculosController@detalle');
    $router->get('modelos/{id}', 'VehiculosController@modelos');

    $router->get('fichas_tecnicas', 'VehiculosController@fichas_tecnicas');
    $router->get('ficha_tecnica/{slug}', 'VehiculosController@ficha_tecnica');
    
    $router->get('accesorios', 'VehiculosController@accesorios');
    $router->get('accesorio/{slug}', 'VehiculosController@accesorio');

    $router->post('comparar_vehiculo_pdf', 'ComparadorController@generate_vehiculo');
    $router->post('comparar_ficha_pdf', 'ComparadorController@generate_ficha');
    
    $router->get('servicios', 'OtrosController@getServicios');
    $router->get('concesionarios', 'OtrosController@concesionarios');
    $router->get('comunidad', 'ComunidadController@show');
    $router->get('pregunta/{slug}', 'ComunidadController@detalle');


});