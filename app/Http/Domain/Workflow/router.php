<?php
/** @var Laravel\Lumen\Routing\Router $router */

$router->group(['as' => 'request', 'prefix' => 'request'], function ($router) {
    $router->get('/', ['as' => 'index', 'uses' => 'WorkflowRequestController@index']);
    $router->get('/{id}', ['as' => 'show', 'uses' => 'WorkflowRequestController@show']);
    $router->put('/{id}/approval', ['as' => 'approval', 'uses' => 'WorkflowRequestController@approval']);

});

$router->get('/reject', ['as' => 'reject.index', 'uses' => 'WorkflowRejectController@index']);
$router->post('/bulk-update', ['as' => 'bulkUpdate', 'uses' => 'WorkflowController@bulkUpdate']);
$router->get('/approved', ['as' => 'approved', 'uses' => 'WorkflowController@approved']);
