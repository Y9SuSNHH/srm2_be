<?php
/** @var Laravel\Lumen\Routing\Router $router */

$router->group(['prefix' => 'g120', 'as' => 'g120'], function ($router) {
    $router->get('/', ['as' => 'index', 'uses' => 'G120Controller@index']);
    $router->get('/download-init', ['as' => 'downloadInit', 'uses' => 'G120Controller@downloadInit']);
    $router->get('/export', ['as' => 'export', 'uses' => 'G120Controller@exportG120']);
    $router->put('/update',['as' =>'update','uses' => 'G120Controller@manageEngagementProcesses']);
});

$router->group(['prefix' => 'g820', 'as' => 'g820'], function ($router) {
    $router->get('/', ['as' => 'index', 'uses' => 'G820Controller@index']);
    $router->get('/download-init', ['as' => 'downloadInit', 'uses' => 'G820Controller@downloadInit']);
    $router->get('/export', ['as' => 'export', 'uses' => 'G820Controller@exportG820']);
});

$router->group(['prefix' => 'p825', 'as' => 'p825'], function ($router) {
    $router->get('/', ['as' => 'index', 'uses' => 'P825Controller@index']);
    $router->get('/download-init', ['as' => 'downloadInit', 'uses' => 'P825Controller@downloadInit']);
    $router->get('/export', ['as' => 'export', 'uses' => 'P825Controller@exportP825']);
});

$router->group(['prefix' => 'p845', 'as' => 'p845'], function ($router) {
    $router->get('/', ['as' => 'index', 'uses' => 'P845Controller@index']);
    $router->get('/download-init', ['as' => 'downloadInit', 'uses' => 'P845Controller@downloadInit']);
    $router->get('/export', ['as' => 'export', 'uses' => 'P845Controller@exportP845']);
});

$router->group(['prefix' => 'f111', 'as' => 'f111'], function ($router) {
    $router->get('/download-init', ['as' => 'downloadInit', 'uses' => 'F111Controller@downloadInit']);
    $router->get('/export', ['as' => 'export', 'uses' => 'F111Controller@downloadTemplate']);

    $router->get('/upload/init', ['as' => 'uploadInit', 'uses' => 'F111Controller@uploadInit']);
    $router->post('/upload/validate', ['as' => 'uploadValidate', 'uses' => 'F111Controller@uploadValidate']);
    $router->put('/upload/store', ['as' => 'uploadStore', 'uses' =>'F111Controller@uploadStore']);
});