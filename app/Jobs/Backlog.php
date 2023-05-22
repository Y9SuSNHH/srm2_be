<?php

namespace App\Jobs;

use App\Helpers\JobBacklog;
use App\Http\Domain\Common\Services\Backlog\ApprovalWorkflowService;
use App\Http\Enum\WorkDiv;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Backlog implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /** @var JobBacklog */
    private $job_backlog;

    /**
     * Create a new job instance.
     * Backlog constructor.
     *
     * @param JobBacklog $job_backlog
     * @return void
     */
    public function __construct(JobBacklog $job_backlog)
    {
        $this->job_backlog = $job_backlog;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->job_backlog->divs() as $work_div) {
            switch ($work_div) {
                case WorkDiv::APPROVAL_EDIT_STUDENT:
                    ApprovalWorkflowService::call($this->job_backlog->idList($work_div));
                    break;
            }
        }
    }
}
