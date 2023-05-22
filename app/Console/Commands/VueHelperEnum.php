<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class VueHelperEnum extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vue_helper:enum {path=""}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    public function handle()
    {
        try {
            $dir_path = base_path('app/Http/Enum');
            $results = [];
            $d = dir($dir_path);

            while (false !== ($entry = $d->read())) {
                $base_class = substr($entry, 0, strpos($entry, '.'));
                $classname = 'App\\Http\\Enum\\' . $base_class;
                if (class_exists($classname) && __CLASS__ !== $classname && method_exists($classname, 'toArray')) {
                    $key = snake_to_camel(preg_replace('/(.)([A-Z])/', '\\1_\\2', $base_class));
                    $results[$key] = call_user_func([$classname, 'toArray']);

                    if (is_array($results[$key])) {
                        $results[$key]['_list'] = call_user_func([$classname, 'fetch']);
                    }
                }
            }

            $d->close();
            $path = $this->argument('path');

            if (!realpath($path)) {
                $path = resource_path('helper');
            }

            $filename = $path . '/enum.json';

            file_put_contents($filename, json_encode($results, JSON_PRETTY_PRINT));
            echo 'create file success: ' . $filename . PHP_EOL;
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }
}