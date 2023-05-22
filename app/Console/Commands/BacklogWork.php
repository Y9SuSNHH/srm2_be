<?php

namespace App\Console\Commands;

use App\Http\Domain\Common\Services\Backlog\ApprovalWorkflowService;
use App\Http\Enum\WorkDiv;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BacklogWork extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backlog:work {backlog=""}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    public function handle()
    {
        try {
            $argument = trim($this->argument('backlog'));

            if (!$argument || '""' === $argument) {
                return;
            }

            app()->configAuth();
            auth()->fakeDefaultsGuard('cmd');
            $backlog = json_decode($argument, true);

            if (is_array($backlog)) {
                echo 'Execute backlog: ' . json_encode($backlog);
                foreach ($backlog as $work_div => $backlog_ids) {
                    switch ($work_div) {
                        case WorkDiv::APPROVAL_EDIT_STUDENT:
                            ApprovalWorkflowService::call($backlog_ids);
                            Log::info('Success to execute backlog', ['div' => WorkDiv::fromOptional($work_div)->getKey()]);
                            break;
                    }
                }
            } else {
                echo "Invalid input: $argument";
            }
        } catch (\Exception $e) {
            echo $e->getMessage() . $this->printTrace($e->getTrace());
        }
    }

    /**
     * @param array $traces
     * @param string $e
     * @return string
     */
    private function printTrace(array $traces, $e = ''): string
    {
        $out = '';
        foreach ($traces as $i => $trace) {
            $out.= PHP_EOL. "$e#$i " .$trace['file']. ' ('.$trace['line'].'): ';
            unset($trace['file']);
            unset($trace['line']);

            foreach ($trace as $key => $value) {
                if (is_array($value)) {
                    $out.= $this->printTrace($value, "#$i");
                } elseif (is_string($value)) {
                    $out.= " [$key] $value";
                } else {
                    $out .= json_encode($value);
                }
            }
        }

        return $out;
    }

}
