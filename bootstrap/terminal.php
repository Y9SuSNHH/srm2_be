<?php

require __DIR__.'/common.php';

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/
/** @var \Laravel\Lumen\Application $app */
$app->register(App\Providers\AppLumenSwooleServiceProvider::class);

return $app;
