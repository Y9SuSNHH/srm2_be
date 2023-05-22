<?php

/** @var Laravel\Lumen\Routing\Router $router */

$router->group(['as' => 'registration'], function ($router) {
    $router->get('/', ['as' => 'index', 'uses' => 'RegistrationController@index']);
    $router->put('/{id}/update', ['as' => 'update', 'uses' => 'RegistrationController@update']);
    $router->delete('/{id}/delete', ['as' => 'delete', 'uses' => 'RegistrationController@delete']);
    $router->get('/{id}/info', ['as' => 'info', 'uses' => 'RegistrationController@getById']);
    
    $router->get('/{id}/export', ['as' => 'export', 'uses' => 'RegistrationController@exportById']);
    $router->get('/download-init', ['as' => 'downloadInit', 'uses' => 'RegistrationController@downloadInit']);
});
