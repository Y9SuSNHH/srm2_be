<?php

/** @var Laravel\Lumen\Routing\Router $router */

$router->get('/school', ['as' => 'api.school.index', 'uses' => 'SchoolController@index']);
$router->post('/register', ['as' => 'api.register', 'uses' => 'RegisterController@handleRegister']);
$router->get('/administrative-unit', ['as' => 'api.administrative', 'uses' => 'AdministrativeController@index']);
$router->get('/role-login', ['as' => 'api.roleLogin.index', function () {
    $roles = \App\Eloquent\Role::query()->get(['authority', 'name', 'id']);

    return json_response(true, [
        'roleOptions' => $roles->map(function ($role) {
            return ['value' => $role->id, 'text' => $role->name];
        }),
        'authority'   => $roles->pluck('authority', 'id'),
    ]);
}]);
$router->get('/school/{id}', ['as' => 'api.school.show', 'uses' => 'SchoolController@show']);
$router->post('/login', ['as' => 'api.login', 'uses' => 'AuthController@login']);
$router->get('/student/grade', ['as' => 'api.student.grade', 'uses' => 'StudentController@grade']);

$router->group(['middleware' => 'auth:api', 'as' => 'api'], function ($router) {
    /** @var Laravel\Lumen\Routing\Router $router */
    $router->get('/auth-info', ['as' => 'authInfo', 'uses' => 'AuthController@authInfo']);
    $router->put('/auth-change-password', ['as' => 'authChangePassword', 'uses' => 'AuthController@changePassword']);
    $router->post('/logout', ['as' => 'logout', 'uses' => 'AuthController@logout']);
    $router->post('/school/create', ['as' => 'school.create', 'uses' => 'SchoolController@create']);
    $router->put('/school/{id}/update', ['as' => 'school.update', 'uses' => 'SchoolController@update']);
    $router->delete('/school/{id}/delete', ['as' => 'school.delete', 'uses' => 'SchoolController@delete']);

    $router->group(['prefix' => 'user', 'as' => 'user'], function ($router) {
        $router->get('/', ['as' => 'index', 'uses' => 'UserController@index']);
        $router->post('/create', ['as' => 'create', 'uses' => 'UserController@create']);
        $router->put('/{id}/update', ['as' => 'update', 'uses' => 'UserController@update']);
        $router->delete('/{id}/delete', ['as' => 'delete', 'uses' => 'UserController@delete']);
    });

    $router->group(['prefix' => 'role', 'as' => 'role'], function ($router) {
        $router->get('/', ['as' => 'index', 'uses' => 'RoleController@index']);
        $router->get('/{id}', ['as' => 'show', 'uses' => 'RoleController@show']);
        $router->post('/create', ['as' => 'create', 'uses' => 'RoleController@create']);
        $router->put('/{id}/update', ['as' => 'update', 'uses' => 'RoleController@update']);
        $router->delete('/{id}/delete', ['as' => 'delete', 'uses' => 'RoleController@delete']);
        $router->put('/{id}/assign', ['as' => 'assign', 'uses' => 'RoleController@assign']);
        $router->put('/{id}/assign-permission', ['as' => 'assignPermission', 'uses' => 'RoleController@assignPermission']);
    });

    $router->group(['prefix' => 'permission', 'as' => 'permission'], function ($router) {
        $router->get('/', ['as' => 'index', 'uses' => 'PermissionController@index']);
        $router->get('/create', ['as' => 'createForm', 'uses' => 'PermissionController@createForm']);
        $router->get('/{id}', ['as' => 'show', 'uses' => 'PermissionController@show']);
        $router->post('/create', ['as' => 'create', 'uses' => 'PermissionController@create']);
        $router->put('/{id}/update', ['as' => 'update', 'uses' => 'PermissionController@update']);
        $router->delete('/{id}/delete', ['as' => 'delete', 'uses' => 'PermissionController@delete']);
        $router->put('/{id}/assign-role', ['as' => 'assignRole', 'uses' => 'PermissionController@assignRole']);
        $router->put('/{id}/assign-user', ['as' => 'assignUser', 'uses' => 'PermissionController@assignUser']);
    });

    $router->group(['prefix' => 'staff', 'as' => 'staff'], function ($router) {
        $router->get('/options', 'StaffController@options');
    });

    $router->get('enum/{param}', function ($param) {
        try {
            return json_response(true, call_user_func(['\\App\\Http\\Enum\\' . pascal_case($param), 'fetch']));
        } catch (Throwable $exception) {
            return json_response(false, [], $exception->getMessage());
        }
    });

    $router->get('enum/{param1}/{param2}', function ($param1, $param2) {
        try {
            return json_response(true, call_user_func(['\\App\\Http\\Enum\\' . pascal_case($param1) . '\\' . pascal_case($param2), 'fetch']));
        } catch (Throwable $exception) {
            return json_response(false, [], $exception->getMessage());
        }
    });

    $router->get('/backlog-tasks', ['as' => 'backlogTasks.index', 'uses' => 'BacklogController@index']);
    $router->put('/backlog-retry', ['as' => 'backlogTasks.retry', 'uses' => 'BacklogController@retry']);
    $router->get('/storage-files/{id}/download', ['as' => 'storageFiles.download', 'uses' => 'StorageFileController@download']);
    $router->post('/storage-files/download/init', ['as' => 'storageFiles.downloadInit', 'uses' => 'StorageFileController@downloadInit']);
});
