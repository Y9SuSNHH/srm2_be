<?php

/** @var Laravel\Lumen\Routing\Router $router */

$router->group(['as' => 'petitions', 'prefix' => 'petitions'], static function ($router) {
    $router->group(['as' => 'ignoreLearningModule', 'prefix' => 'ignore-learning-module'], static function ($router) {
        $router->get('/import/template/download', ['as' => 'templateDownload', 'uses' => 'IgnoreLearningModuleController@templateDownload']);
        $router->get('/export/download', ['as' => 'export', 'uses' => 'IgnoreLearningModuleController@export']);
        $router->get('/import/init', ['as' => 'importInit', 'uses' => 'IgnoreLearningModuleController@importInit']);
        $router->get('/', ['as' => 'index', 'uses' => 'IgnoreLearningModuleController@index']);
        $router->post('/import/template/init', ['as' => 'templateInit', 'uses' => 'IgnoreLearningModuleController@templateInit']);
        $router->post('/export/init', ['as' => 'exportInit', 'uses' => 'IgnoreLearningModuleController@exportInit']);
        $router->post('/import/validator', ['as' => 'importValidator', 'uses' => 'IgnoreLearningModuleController@importValidator']);
        $router->post('/import', ['as' => 'import', 'uses' => 'IgnoreLearningModuleController@import']);
        $router->delete('/{id}', ['as' => 'destroy', 'uses' => 'IgnoreLearningModuleController@destroy']);
    });
    $router->get('/export/download', ['as' => 'export', 'uses' => 'PetitionController@export']);
    $router->get('', ['as' => 'index', 'uses' => 'PetitionController@index']);
    $router->get('/{id}', ['as' => 'show', 'uses' => 'PetitionController@show']);
    $router->post('/export/init', ['as' => 'exportInit', 'uses' => 'PetitionController@exportInit']);
    $router->put('/{id}', ['as' => 'update', 'uses' => 'PetitionController@update']);
    $router->patch('/{id}', ['as' => 'update', 'uses' => 'PetitionController@update']);
    $router->delete('/{id}', ['as' => 'destroy', 'uses' => 'PetitionController@destroy']);
});

$router->group(['as' => 'student'], function ($router) {
    $router->get('/example/download', ['as' => 'downloadTemplate', 'uses' => 'StudentController@downloadTemplate']);
    $router->get('/importInit', ['as' => 'importInit', 'uses' => 'StudentController@importInit']);
    $router->get('/download-init', ['as' => 'downloadInit', 'uses' => 'StudentController@downloadInit']);
    $router->get('/export/download', ['as' => 'export', 'uses' => 'StudentController@export']);
    $router->get('/export-g110/download', ['as' => 'exportG110', 'uses' => 'StudentController@exportG110']);
    $router->get('/', ['as' => 'index', 'uses' => 'StudentController@index']);
    $router->get('/{id}', ['as' => 'show', 'uses' => 'StudentController@show']);
    $router->get('/{id}/grades', ['as' => 'showGrades', 'uses' => 'StudentController@showGrades']);
    $router->get('/{id}/tuition', ['as' => 'showTuition', 'uses' => 'StudentController@showTuition']);
    $router->post('/export/init', ['as' => 'exportInit', 'uses' => 'StudentController@exportInit']);
    $router->put('/{id}/student-profile', ['as' => 'updateStudentProfile', 'uses' => 'StudentController@updateStudentProfile']);
    $router->patch('/{id}/student-profile', ['as' => 'updateStudentProfile', 'uses' => 'StudentController@updateStudentProfile']);
    $router->put('/{id}/profile', ['as' => 'updateProfile', 'uses' => 'StudentController@updateProfile']);
    $router->put('/{id}/learning-module', ['as' => 'updateLearningInfo', 'uses' => 'StudentController@updateLearningInfo']);
    $router->patch('/{id}/profile', ['as' => 'updateProfile', 'uses' => 'StudentController@updateProfile']);
    $router->post('/import', ['as' => 'importProfile', 'uses' => 'StudentController@importProfile']);
    $router->post('/storeImport', ['as' => 'storeImportProfile', 'uses' => 'StudentController@storeImportProfile']);
//    $router->get('/options', ['as' => 'options', 'uses' => 'AreaController@options']);
//    $router->get('/{id}', ['as' => 'show', 'uses' => 'AreaController@show']);
//    $router->post('/create', ['as' => 'create', 'uses' => 'AreaController@create']);
//    $router->delete('/delete/{id}', ['as' => 'delete', 'uses' => 'AreaController@delete']);
});

$router->get('/{student_id}/status', ['as' => 'status', 'uses' => 'StudentStatusController@index']);
$router->put('/{student_id}/workflow-store', ['as' => 'workflowStore', 'uses' => 'StudentApprovalRequestController@workflowStore']);

//$router->post('/{id}/petitions', ['as' => 'store', 'uses' => 'PetitionController@store']);

$router->group(['as' => 'petitions'], function ($router) {
    $router->group(['as' => 'ignoreLearningModule'], static function ($router) {
        $router->post('/{id}/petitions/ignoreLearningModule', ['as' => 'store', 'uses' => 'IgnoreLearningModuleController@store']);
    });
    $router->post('/{id}/petitions', ['as' => 'store', 'uses' => 'PetitionController@store']);
});

$router->group(['prefix' => 'learning-process', 'as' => 'learningProcess'], function ($router) {
    $router->get('/chamsocsinhvien', ['as' => 'index', 'uses' => 'LearningProcessController@index']);
    $router->get('/test', ['as' => 'test', 'uses' => 'LearningProcessController@test']);
    $router->post('/create', ['as' => 'create', 'uses' => 'LearningProcessController@create']);
});

$router->group(['prefix' => 'care-history', 'as' => 'careHistory'], function ($router) {
    $router->get('/care-history', ['as' => 'index', 'uses' => 'CareHistoryController@index']);
    $router->post('/create', ['as' => 'create', 'uses' => 'CareHistoryController@create']);
    $router->put('/{id}/update', ['as' => 'update', 'uses' => 'CareHistoryController@update']);
});