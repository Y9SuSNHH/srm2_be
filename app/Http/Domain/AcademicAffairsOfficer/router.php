<?php

/** @var Laravel\Lumen\Routing\Router $router */

$router->group(['prefix' => 'class', 'as' => 'class'], function ($router) {
    $router->get('/', ['as' => 'index', 'uses' => 'ClassController@index']);
    $router->get('/options', ['as' => 'options', 'uses' => 'ClassController@options']);
    $router->get('/major-object', ['as' => 'majorObject', 'uses' => 'ClassController@getMajorAndObject']);
    $router->get('/{id}', ['as' => 'show', 'uses' => 'ClassController@show']);
    $router->post('/create', ['as' => 'create', 'uses' => 'ClassController@create']);
    $router->put('/{id}/update', ['as' => 'update', 'uses' => 'ClassController@update']);
    $router->delete('/{id}/delete', ['as' => 'delete', 'uses' => 'ClassController@delete']);
    $router->post('/create-batch', ['as' => 'createBatch', 'uses' => 'ClassController@createBatch']);
    $router->put('/update-learning-management', ['as' => 'updateLearningManagement', 'uses' => 'ClassController@updateLearningManagement']);
});

$router->group(['prefix' => 'grade', 'as' => 'grade'], function ($router) {
    $router->get('/', ['as' => 'index', 'uses' => 'GradeController@index']); // list, phân trang
    $router->get('/classroom', ['as' => 'classroom', 'uses' => 'GradeController@getClassroom']); // list, phân trang
    $router->get('/import/init', ['as' => 'importInit', 'uses' => 'GradeController@importInit']); // khởi tạo import
    $router->get('/{id}', ['as' => 'show', 'uses' => 'GradeController@show']); // xem chi tiết
    $router->post('/import/validate', ['as' => 'importValidate', 'uses' => 'GradeController@importValidate']); // kiểm tra file import, get preview
    $router->put('/import/store', ['as' => 'importStore', 'uses' => 'GradeController@importStore']); // lưu điểm
    $router->put('/{id}/update', ['as' => 'update', 'uses' => 'GradeController@update']); // sửa điểm 1 sinh viên
    $router->put('/{storage_file_id}/delete-init', ['as' => 'deleteInit', 'uses' => 'GradeController@deleteInit']);
    $router->delete('/{storage_file_id}/delete', ['as' => 'delete', 'uses' => 'GradeController@delete']);
});

$router->group(['prefix' => 'handovers', 'as' => 'handovers'], function ($router) {
    $router->get('/{id}/student-profiles', ['as' => 'studentsProfiles.index', 'uses' => 'HandoverController@indexStudent']);
    $router->post('/{id}/student-profiles', ['as' => 'studentsProfiles.update', 'uses' => 'HandoverController@updateStudent']);
    $router->put('/student-profiles/{id}', ['as' => 'studentsProfiles.destroy', 'uses' => 'HandoverController@destroyStudent']);

    $router->get('/', ['as' => 'index', 'uses' => 'HandoverController@index']);
    $router->get('/{id}/export/download', ['as' => 'export', 'uses' => 'HandoverController@export']);
    $router->post('/{id}/export/init', ['as' => 'exportInit', 'uses' => 'HandoverController@exportInit']);
    $router->post('/', ['as' => 'store', 'uses' => 'HandoverController@store']);
    $router->put('/{id}/delete', ['as' => 'destroy', 'uses' => 'HandoverController@destroy']);
    $router->put('/{id}', ['as' => 'update', 'uses' => 'HandoverController@update']);
    $router->patch('/{id}/lock', ['as' => 'updateLock', 'uses' => 'HandoverController@updateLock']);
    $router->patch('/{id}', ['as' => 'update', 'uses' => 'HandoverController@update']);
});