<?php

/** @var Laravel\Lumen\Routing\Router $router */

$router->group(['prefix' => 'area', 'as' => 'area'], function ($router) {
    $router->get('/', ['as' => 'index', 'uses' => 'AreaController@index']);
    $router->get('/options', ['as' => 'options', 'uses' => 'AreaController@options']);
    $router->get('/{id}', ['as' => 'show', 'uses' => 'AreaController@show']);
    $router->post('/create', ['as' => 'create', 'uses' => 'AreaController@create']);
    $router->put('/{id}/update', ['as' => 'update', 'uses' => 'AreaController@update']);
    $router->delete('/{id}/delete', ['as' => 'delete', 'uses' => 'AreaController@delete']);
});

$router->group(['prefix' => 'enrollment_wave', 'as' => 'enrollmentWave'], function ($router) {
    $router->get('/', ['as' => 'index', 'uses' => 'EnrollmentWaveController@index']);
    $router->get('/options', ['as' => 'options', 'uses' => 'EnrollmentWaveController@options']);
    $router->get('/{id}', ['as' => 'show', 'uses' => 'EnrollmentWaveController@show']);
    $router->post('/create', ['as' => 'create', 'uses' => 'EnrollmentWaveController@create']);
    $router->put('/{id}/update', ['as' => 'update', 'uses' => 'EnrollmentWaveController@update']);
    $router->delete('/{id}/delete', ['as' => 'delete', 'uses' => 'EnrollmentWaveController@delete']);
});

$router->group(['prefix' => 'major', 'as' => 'major'], function ($router) {
    $router->get('/', ['as' => 'index', 'uses' => 'MajorController@index']);
    $router->get('/options', ['as' => 'options', 'uses' => 'MajorController@options']);
    $router->get('/{id}', ['as' => 'show', 'uses' => 'MajorController@show']);
    $router->put('/object-map/{id}', ['as' => 'objectMap', 'uses' => 'MajorController@updateObjectMap']);
    $router->post('/create', ['as' => 'create', 'uses' => 'MajorController@create']);
    $router->put('/{id}/update', ['as' => 'update', 'uses' => 'MajorController@update']);
    $router->delete('/{id}/delete', ['as' => 'delete', 'uses' => 'MajorController@delete']);
});

$router->group(['prefix' => 'enrollment-object', 'as' => 'enrollmentObject'], function ($router) {
    $router->get('/', ['as' => 'index', 'uses' => 'EnrollmentObjectController@index']);
    $router->get('/options', ['as' => 'options', 'uses' => 'EnrollmentObjectController@options']);
//    $router->get('/object-map', ['as' => 'objectMap', 'uses' => 'EnrollmentObjectController@objectMap']);
    $router->get('/{id}', ['as' => 'show', 'uses' => 'EnrollmentObjectController@show']);
    $router->post('/create', ['as' => 'create', 'uses' => 'EnrollmentObjectController@create']);
    $router->put('/{id}/update', ['as' => 'update', 'uses' => 'EnrollmentObjectController@update']);
    $router->delete('/{id}/delete', ['as' => 'delete', 'uses' => 'EnrollmentObjectController@delete']);
    $router->put('/object-map', ['as' => 'objectMap', 'uses' => 'EnrollmentObjectController@updateObjectMap']);
});

$router->group(['prefix' => 'learning-module', 'as' => 'learningModule'], function ($router) {
    $router->get('/', ['as' => 'index', 'uses' => 'LearningModuleController@index']);
});

$router->group(['prefix' => 'curriculum', 'as' => 'curriculum'], function ($router) {
    $router->get('/', ['as' => 'index', 'uses' => 'CurriculumController@index']);
    $router->post('/create', ['as' => 'create', 'uses' =>'CurriculumController@create']);
    $router->put('/update/{id}', ['as' => 'update', 'uses' =>'CurriculumController@update']);
    $router->delete('/{id}/delete', ['as' => 'delete', 'uses' =>'CurriculumController@delete']);
});

$router->group(['prefix' => 'study_session', 'as' => 'studySession'], function ($router) {
    $router->get('/', ['as' => 'index', 'uses' => 'StudySessionController@index']);
    $router->get('/download/init', ['as' => 'downloadInit', 'uses' => 'StudySessionController@downloadInit']);
    $router->get('/download/template', ['as' => 'downloadTemplate', 'uses' => 'StudySessionController@downloadTemplate']);
    $router->get('/upload/init', ['as' => 'uploadInit', 'uses' => 'StudySessionController@uploadInit']);
    $router->get('/{id}', ['as' => 'detail', 'uses' => 'StudySessionController@show']);
    $router->put('/{id}/update', ['as' => 'update', 'uses' => 'StudySessionController@update']);
    $router->post('/upload/validate', ['as' => 'uploadValidate', 'uses' => 'StudySessionController@uploadValidate']);
    $router->put('/upload/store', ['as' => 'uploadStore', 'uses' =>'StudySessionController@uploadStore']);
});

$router->group(['prefix' => 'study_plan', 'as' => 'studyPlan'], function ($router) {
    $router->get('/', ['as' => 'index', 'uses' => 'StudyPlanController@index']);
    $router->get('/options', ['as' => 'options', 'uses' => 'StudyPlanController@options']);
    $router->get('/download/init', ['as' => 'downloadInit', 'uses' => 'StudyPlanController@downloadInit']);
    $router->get('/download/template', ['as' => 'downloadTemplate', 'uses' => 'StudyPlanController@downloadTemplate']);
    $router->get('/params', ['as' => 'params', 'uses' => 'StudyPlanController@sendParams']);
    $router->get('/{id}', ['as' => 'show', 'uses' => 'StudyPlanController@show']);
    $router->post('/create', ['as' => 'create', 'uses' => 'StudyPlanController@create']);
    $router->put('/{id}/update', ['as' => 'update', 'uses' => 'StudyPlanController@update']);
    $router->delete('/{id}/delete', ['as' => 'delete', 'uses' => 'StudyPlanController@delete']);
});

$router->group(['prefix' => 'period', 'as' => 'period'], function ($router) {
    $router->get('/', ['as' => 'index', 'uses' => 'PeriodController@index']);
    $router->get('/options', ['as' => 'options', 'uses' => 'PeriodController@options']);
    $router->delete('/{id}/delete', ['as' => 'delete', 'uses' => 'PeriodController@delete']);
});

$router->group(['prefix' => 'subject', 'as' => 'subject'], function ($router) {
    $router->get('/', ['as' => 'index', 'uses' => 'SubjectController@index']);
    $router->get('/options', ['as' => 'options', 'uses' => 'SubjectController@options']);
    $router->get('/{id}', ['as' => 'show', 'uses' => 'SubjectController@show']);
    $router->post('/create', ['as' => 'create', 'uses' => 'SubjectController@create']);
    $router->put('/{id}/update', ['as' => 'update', 'uses' => 'SubjectController@update']);
    $router->delete('/{id}/delete', ['as' => 'delete', 'uses' => 'SubjectController@delete']);
});

$router->group(['prefix' => 'learning_module', 'as' => 'learningModule'], function ($router) {
    $router->get('/', ['as' => 'index', 'uses' => 'LearningModuleController@index']);
    $router->get('/options', ['as' => 'options', 'uses' => 'LearningModuleController@options']);
    $router->get('/{id}', ['as' => 'show', 'uses' => 'LearningModuleController@show']);
    $router->post('/create', ['as' => 'create', 'uses' => 'LearningModuleController@create']);
    $router->put('/{id}/update', ['as' => 'update', 'uses' => 'LearningModuleController@update']);
    $router->delete('/{id}/delete', ['as' => 'delete', 'uses' => 'LearningModuleController@delete']);
});

$router->group(['prefix' => 'major_object_map', 'as' => 'majorObjectMap'], function ($router) {
    $router->get('/', ['as' => 'index', 'uses' => 'MajorObjectMapController@index']);
    $router->get('/options', ['as' => 'options', 'uses' => 'MajorObjectMapController@options']);
    $router->get('/{id}', ['as' => 'show', 'uses' => 'MajorObjectMapController@show']);
    $router->post('/create', ['as' => 'create', 'uses' => 'MajorObjectMapController@create']);
    // $router->put('/{id}/update', ['as' => 'update', 'uses' => 'MajorObjectMapController@update']);
    // $router->delete('/{id}/delete', ['as' => 'delete', 'uses' => 'MajorObjectMapController@delete']);
});