<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class VueHelperEndpoint extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vue_helper:endpoint {path=""}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    public function handle()
    {
        try {
            $this->loadRouter();
            $routers = app()->router->getRoutes();
            $results = (object)[];

            foreach ($routers as $url => $router) {
                if (isset($router['action']['as'])) {
                    $arr = explode('.', $router['action']['as']);
                    $point = $results;

                    while ($key = array_shift($arr)) {
                        if (!isset($point->{$key})) {
                            $point->{$key} = (object)[];
                        }

                        $point = $point->{$key};
                    }
                }
            }

            $results = json_decode(json_encode($results), true);

            foreach ($routers as $url => $router) {
                if (isset($router['action']['as'])) {
                    $arr = explode('.', $router['action']['as']);
                    $url = str_ireplace(['GET/', 'POST/', 'PUT/', 'PATCH/', 'DELETE/', 'OPTIONS/'], '', $url);
                    $this->assignUrl($results, $arr, $url);
                }
            }

            $path = $this->argument('path');

            if (!realpath($path)) {
                $path = resource_path('helper');
            }

            $filename = $path . '/endpoint.json';

            file_put_contents($filename, json_encode($results, JSON_PRETTY_PRINT));
            echo 'create file success: ' . $filename . PHP_EOL;
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    private function assignUrl(array &$array, array $keys, $url)
    {
        if (count($array) === 0) {
            $array = $url;
        } else {
            $key = array_shift($keys);

            if (isset($array[$key]) && is_array($array[$key]) && is_array($keys)) {
                $this->assignUrl($array[$key], $keys, $url);
            }
        }
    }

    private function loadRouter()
    {
        /** @var \Laravel\Lumen\Application $app */
        $app = app();
        $domains = config('app.domains', []);
        $base_domain_namespace = \App\Application::BASE_DOMAIN_NAMESPACE;

        foreach ($domains as $key => $group) {
            $dirname = pascal_case($key);
            $path = realpath($app->basePath(lcfirst(str_replace('\\', '/', $base_domain_namespace)).  $dirname .'/router.php'));

            if (!$path) {
                continue;
            }

            $app->router->group(array_filter(array_replace([
                'namespace' => $base_domain_namespace . $dirname .'\\Controllers'
            ], $group, [
                'prefix' => preg_replace('/(^web$)|[^a-z0-9\-_]*/', '', strtolower($key))
            ])), function ($router) use ($path, $app) {
                /** @var \Laravel\Lumen\Routing\Router $router */
                require_once $path;
            });
        }
    }
}
