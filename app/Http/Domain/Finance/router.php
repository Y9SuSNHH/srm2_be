<?php

/** @var Laravel\Lumen\Routing\Router $router */

$router->group(['as' => 'finance'], function ($router) {
    $router->get('/class', ['as' => 'class', 'uses' => 'FinanceController@class']);
    $router->get('/filter', ['as' => 'filter', 'uses' => 'FinanceController@filter']);
    $router->get('/student', ['as' => 'student', 'uses' => 'FinanceController@student']);
    $router->put('/{id}/update', ['as' => 'update', 'uses' => 'FinanceController@update']);
    $router->delete('/{id}/delete', ['as' => 'delete', 'uses' => 'FinanceController@delete']);
    $router->get('/tuition', ['as' => 'tuition', 'uses' => 'FinanceController@tuition']);
    $router->get('/filterStudent', ['as' => 'filterStudent', 'uses' => 'FinanceController@filterStudent']);
    $router->get('/studentClass', ['as' => 'studentClass', 'uses' => 'FinanceController@studentClass']);
    $router->get('/semesterClass', ['as' => 'semesterClass', 'uses' => 'FinanceController@semesterClass']);
    $router->get('/receiveSemester', ['as' => 'receiveSemester', 'uses' => 'FinanceController@receiveSemester']);
    $router->post('/export/init', ['as' => 'exportInit', 'uses' => 'FinanceController@exportInit']);
    $router->get('/export/download', ['as' => 'export', 'uses' => 'FinanceController@export']);
    $router->get('/export/transaction/download', ['as' => 'exportTransaction', 'uses' => 'FinanceController@exportTransaction']);
    $router->get('/export/tuition/download', ['as' => 'exportTuition', 'uses' => 'FinanceController@exportTuition']);
    $router->get('/export/tuitionClass/download', ['as' => 'exportTuitionClass', 'uses' => 'FinanceController@exportTuitionClass']);
});

$router->group(['as' => 'transaction', 'prefix' => 'transaction'], function ($router) {
    $router->post('/create', ['as' => 'create', 'uses' => 'TransactionController@create']);
});