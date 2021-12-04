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
    $router->post('vehicle_remove', 'VehiculosController@remove_vehicle');
    $router->post('vehicle_sold', 'VehiculosController@sold_vehicle');
    $router->post('upload_vehicle_image', 'VehiculosController@upload_vehicle_image');
    $router->post('upload_vehicle_peritaje', 'VehiculosController@upload_vehicle_peritaje');

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
    $router->post('login-admin', 'AuthController@login_admin');

    $router->get('sitemap', 'OtrosController@sitemap');
    $router->get('in-app-browser/{slug}', 'OtrosController@inAppBrowser');

    $router->get('home', 'HomeController@show');
    $router->get('config', 'HomeController@config');
    $router->post('newsletter', 'HomeController@newsletter');

    $router->get('vehiculos', 'VehiculosController@find');
    $router->get('vehiculo/{slug}', 'VehiculosController@detalle');
    $router->get('modelos/{id}', 'VehiculosController@modelos');
    $router->get('marcas/{id}', 'VehiculosController@marcas');
    $router->post('form_contact', 'OtrosController@form_contact');

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

    $router->post('generar-restablecer-contrasena', 'OtrosController@restablecer_contrasena_link');
    $router->post('restablecer-contrasena', 'OtrosController@restablecer_contrasena');
    $router->get('validar-token/{token}', 'OtrosController@validar_token');

    $router->get('pattners/{slug}', 'PatternsController@getPatterns');
    $router->put('click-pattner/{id}', 'PatternsController@onClick');

    $router->get('informacion-documentos', 'DocumentsController@informationDocuments');
    $router->post('documento-compra-venta', 'DocumentsController@salesPurchaseDocument');
    $router->post('documento-mandato', 'DocumentsController@mandateDocument');
    $router->post('documento-tramite', 'DocumentsController@procedureDocument');
});

$router->group(['prefix' => 'admin',  'middleware' => 'admin'], function () use ($router){
    $router->get('users', 'OtrosController@get_all_users');
    $router->get('users/{id}', 'OtrosController@get_by_user');
    $router->post('create-user', 'OtrosController@create_user');
    $router->put('users', 'OtrosController@updated_user');
    $router->put('active-user', 'OtrosController@active_user');
    $router->put('locked-user', 'OtrosController@bloqued_user');
    $router->get('roles-user', 'OtrosController@get_roles');

    $router->get('vehicles', 'VehiculosController@get_all_vehicles');
    $router->get('form-updated-vehicle/{id}', 'VehiculosController@get_by_vehicle');
    $router->post('updated-vehicle', 'VehiculosController@update_vehicle_admin');
    $router->put('dependable-vehicle', 'VehiculosController@dependable_vehicle');
    $router->post('approve-vehicle', 'VehiculosController@approve_vehicle');
    $router->post('approve-promotion-vehicle', 'VehiculosController@approve_promotion');
    $router->post('remove-vehicle-admin', 'VehiculosController@remove_vehicle_admin');

    $router->get('technical-sheets', 'FichaTecnicaController@get_all_technical_sheets');
    $router->get('form-updated-technical-sheets/{id}', 'FichaTecnicaController@get_by_technical_sheets');
    $router->get('form-technical-sheets', 'FichaTecnicaController@form_technical_sheets');
    $router->post('create-technical-sheets', 'FichaTecnicaController@create_technical_sheets');
    $router->post('updated-technical-sheets', 'FichaTecnicaController@update_technical_sheets');
    $router->put('active-technical-sheets', 'FichaTecnicaController@inactivate');
    $router->post('delete-technical-sheets', 'FichaTecnicaController@delete');

    $router->get('dealerships', 'OtrosController@get_all_dealerships');
    $router->post('create-dealerships', 'OtrosController@create_dealerships');
    $router->get('form-update-dealerships/{id}', 'OtrosController@get_by_dealerships');
    $router->post('update-dealerships', 'OtrosController@update_dealerships');
    $router->post('delete-dealerships', 'OtrosController@delete_dealerships');

    $router->get('community', 'ComunidadController@get_all_questions');
    $router->get('news', 'OtrosController@get_all_news'); //crear

    $router->get('services', 'OtrosController@get_all_services');
    $router->post('create-service', 'OtrosController@create_services');
    $router->get('form-update-service/{id}', 'OtrosController@get_by_services');
    $router->post('update-service', 'OtrosController@update_services');
    $router->post('delete-services', 'OtrosController@delete_services');
    
    $router->get('promotion', 'PatternsController@get_all_promotion');
    $router->post('create-promotion', 'PatternsController@create_promotion');
    $router->get('form-update-promotion/{id}', 'PatternsController@get_by_promotion');
    $router->post('update-promotion', 'PatternsController@update_promotion');
    $router->put('active-promotion', 'PatternsController@inactivate');
    $router->post('delete-promotion', 'PatternsController@delete');

    $router->get('configs', 'ConfiguracionesController@configs');
    $router->post('update-configs', 'ConfiguracionesController@update_configs');
    $router->get('permissions', 'ConfiguracionesController@permissions');
    $router->get('form-update-permissions/{id}', 'ConfiguracionesController@get_by_permissions');
    $router->get('form-create-permissions', 'ConfiguracionesController@form_permissions');
    $router->post('update-permissions', 'ConfiguracionesController@update_permissions');
    $router->post('create-permissions', 'ConfiguracionesController@create_permissions');
});