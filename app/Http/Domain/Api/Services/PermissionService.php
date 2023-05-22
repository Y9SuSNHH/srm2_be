<?php

namespace App\Http\Domain\Api\Services;

use Illuminate\Support\Facades\Log;

class PermissionService
{
    /**
     * @return array
     */
    public function guardEloquentList(): array
    {
        try {
            $results = [];
            $d = dir(base_path('app/Eloquent'));
            while (false !== ($entry = $d->read())) {
                if (preg_match('/^([A-Z][a-zA-Z0-9]+)/', $entry, $matches)) {
                    $classname = 'App\\Eloquent\\' . $matches[1];

                    if (class_exists($classname)) {
                        $results[] = $classname;
                    }
                }
            }
            $d->close();

            return $results;
        } catch (\Throwable $exception) {
            Log::error('Fail to get list eloquent', compact('exception'));
            return [];
        }
    }
}