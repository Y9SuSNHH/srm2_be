<?php

/** @var Laravel\Lumen\Routing\Router $router */

$router->group(['as' => 'contact'], function ($router) {
    $router->get('/', ['as' => 'index', 'uses' => 'ContactController@index']);
    $router->post('/create', ['as' => 'create', 'uses' => 'ContactController@create']);
    $router->post('/add', ['as' => 'add', 'uses' => 'ContactController@add']);
    $router->put('/{id}/update', ['as' => 'update', 'uses' => 'ContactController@update']);
    $router->delete('/{id}/delete', ['as' => 'delete', 'uses' => 'ContactController@delete']);
    $router->get('/{id}/link', ['as' => 'link', 'uses' => 'ContactController@link']);
});
