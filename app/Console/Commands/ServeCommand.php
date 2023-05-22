<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class ServeCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'serve';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Serve the application on the PHP development server";

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $host = env('HTTP_HOST');
        if (!$host) {
            $host = $this->input->getOption('host');
        }

        $port = env('HTTP_PORT');
        if (!$port) {
            $port = $this->input->getOption('port');
        }

        $base = $this->laravel->basePath();

        $this->info("Lumen development server started on http://{$host}:{$port}/");

        passthru('"' . PHP_BINARY . '"' . " -S {$host}:{$port} -t \"{$base}/public\"");
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        $url = env('APP_URL');

        return [
            [
                'host', null, InputOption::VALUE_OPTIONAL, 'The host address to serve the application on.', parse_url($url, PHP_URL_HOST)
            ], [
                'port', null, InputOption::VALUE_OPTIONAL, 'The port to serve the application on.', parse_url($url, PHP_URL_PORT)
            ],
        ];
    }

}
