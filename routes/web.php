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

    $router->post('vehicle_insert', 'VehiculosController@insert');
    $router->post('vehicle_edit', 'VehiculosController@edit_vehicle');
    $router->post('vehicle_update', 'VehiculosController@update_vehicle');

    $router->post('accessory_insert', 'AccesoriosController@insert_accessory');
    $router->post('accessory_edit', 'AccesoriosController@edit_accessory');
    $router->post('accessory_update', 'AccesoriosController@update_accessory');

    $router->get('profile/{id}', 'UsuarioController@profile');
    $router->get('publicaciones/{id}', 'UsuarioController@publicaciones');
    $router->get('favoritos/{id}', 'UsuarioController@favoritos');
    $router->get('busquedas/{id}', 'UsuarioController@busquedas');
    $router->post('busqueda_remove', 'UsuarioController@remove_busqueda');
    $router->get('form_producto', 'UsuarioController@form_producto');
    $router->post('comment', 'UsuarioController@make_comment');
    $router->post('favoritos', 'UsuarioController@make_favorito');
    $router->post('favoritos_vehiculo', 'UsuarioController@make_favorito_vehiculo');
    $router->post('remove_favorito_vehiculo', 'UsuarioController@remove_favorito_vehiculo');
    $router->post('remove_favorito_ficha', 'UsuarioController@remove_favorito_ficha');
    $router->post('profile_update', 'UsuarioController@profile_update');

    $router->post('crear_pregunta', 'ComunidadController@createQuestion');

});
//Public routes
$router->group(['prefix' => 'api'], function () use ($router){
    $router->get('home', 'HomeController@show');
    $router->get('config', 'HomeController@config');
    $router->post('newsletter', 'HomeController@newsletter');

    $router->get('vehiculos', 'VehiculosController@find');
    $router->get('vehiculo/{slug}', 'VehiculosController@detalle');
    $router->get('modelos/{id}', 'VehiculosController@modelos');
    $router->get('marcas/{id}', 'VehiculosController@marcas');

    $router->get('fichas_tecnicas', 'FichaTecnicaController@fichas_tecnicas');
    $router->get('ficha_tecnica/{slug}', 'FichaTecnicaController@ficha_tecnica');
    
    $router->get('accesorios', 'VehiculosController@AccesoriosController');
    $router->get('accesorio/{slug}', 'VehiculosController@AccesoriosController');

    $router->post('comparar_vehiculo_pdf', 'ComparadorController@generate_vehiculo');
    $router->post('comparar_ficha_pdf', 'ComparadorController@generate_ficha');
    
    $router->get('servicios', 'OtrosController@getServicios');
    $router->get('concesionarios', 'OtrosController@concesionarios');
    $router->post('financiacion', 'OtrosController@financiacion');
    $router->get('ciudades/{id}', 'OtrosController@get_cities');

    $router->get('comunidad', 'ComunidadController@show');
    $router->get('pregunta/{slug}', 'ComunidadController@detalle');
    $router->get('tags', 'ComunidadController@allTags');
});