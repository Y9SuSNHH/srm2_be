<?php

/** @var Laravel\Lumen\Routing\Router $router */

$router->group(['as' => 'receivable'], function ($router) {
    $router->get('/', ['as' => 'index', 'uses' => 'ReceivableController@index']);
    $router->get('/periods', ['as' => 'periods', 'uses' => 'ReceivableController@fetchPeriod']);
    // $router->get('/semester', ['as' => 'semester', 'uses' => 'ReceivableController@fetchSemester']);
    // $router->get('/classes', ['as' => 'classes', 'uses' => 'ReceivableController@fetchClasses']);
    $router->get('/classrooms', ['as' => 'classrooms', 'uses' => 'ReceivableController@getAllClassroom']);
    $router->get('/staffs', ['as' => 'staffs', 'uses' => 'ReceivableController@getAllQlht']);
    $router->get('/majors', ['as' => 'majors', 'uses' => 'ReceivableController@getAllMajor']);
    $router->get('/{id}', ['as' => 'show', 'uses' => 'ReceivableController@show']);
    $router->post('/classes/store', 'ReceivableController@storeClassroomReceivable');
    $router->post('/{id}/store', 'ReceivableController@storeStudentReceivable'); // lưu vô bảng student_receivable

});