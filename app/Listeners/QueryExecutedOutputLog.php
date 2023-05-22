<?php

namespace App\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

/**
 * Class QueryExecutedOutputLog
 * @package App\Listeners
 */
class QueryExecutedOutputLog
{
    public function handle(QueryExecuted $event)
    {
        if (!(bool)env('APP_DEBUG')) {
            return;
        }

        $bindings = $event->bindings;
        foreach ($bindings as $i => $binding) {
            if ($binding instanceof \DateTime) {
                $bindings[$i] = $binding->format('\'Y-m-d H:i:s\'');
            } else if (is_string($binding)) {
                $bindings[$i] = DB::getPdo()->quote($binding);
            } else if (null === $binding) {
                $bindings[$i] = 'null';
            }
        }
        $query = str_replace(array('%', '?', "\r", "\n", "\t"), array('%%', '%s', ' ', ' ', ' '), $event->sql);
        $query = preg_replace('/\s+/uD', ' ', $query);
        $query = vsprintf($query, $bindings) . ';';
        $date = date('Y-m-d');
        $filename = storage_path("logs/sql-$date.log");

        if ($filename) {
            $resource = fopen($filename,'a');
            if (!$resource) {
                throw new \Exception("cannot create or append file: $filename");
            }

            fputs($resource, '['. date('H:i:s') ."] $query ({$event->time})\n");
            fclose($resource);
        }
    }
}
