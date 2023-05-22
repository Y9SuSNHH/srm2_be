<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SessionTimeoutClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'session_timeout:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    public function handle()
    {
        try {
            $path = storage_path('session');
            dump('start clear file: '.$path);
            if (file_exists($path) && is_dir($path)) {
                $d = dir($path);

                while (false !== ($entry = $d->read())) {
                    $file = $path .DIRECTORY_SEPARATOR. $entry;

                    if ('.gitignore' !== $entry && file_exists($file) && is_file($file)) {
                        $max_life_time = session()->maxLifeTime();
                        $last_modify_time = filemtime($file);

                        if (time() - $last_modify_time > $max_life_time) {
                            unlink($file);
                        }
                    }
                }

                $d->close();
                dump('success clear file');
            } else {
                dump('not found: '. $path);
            }
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }
}
