<?php

namespace App\Helpers;

use App\Http\Domain\Common\Services\BacklogService;

/**
 * Class BacklogHandled
 * @package App\Helpers
 */
abstract class BacklogHandled
{
    /**
     * @param array $backlog_ids
     * @return void
     */
    public static function call(array $backlog_ids): void
    {
        foreach ($backlog_ids as $backlog_id) {
            try {
                $backlog_service = app()->service(\App\Http\Domain\Common\Services\BacklogService::class);
                call_user_func(function () use ($backlog_id, $backlog_service) {
                    $handler = new static();
                    $handler->handle($backlog_id, $backlog_service);
                });
                unset($backlog_service);
            } catch (\Exception $exception) {
                \Illuminate\Support\Facades\DB::table('backlogs')->where('id', $backlog_id)->update([
                    'updated_at' => \Carbon\Carbon::now(),
                    'work_status' => \App\Http\Enum\WorkStatus::FAIL,
                    'note' => $exception->getMessage() .': '. json_encode($exception->getTrace())
                ]);
            }
            sleep(3);
        }
    }

    abstract public function handle(int $backlog_id, \App\Http\Domain\Common\Services\BacklogService $backlog_service);
}
