<?php

namespace App\Jobs;

use App\Http\Domain\Student\Services\LearningProcessService;
use App\Http\Domain\TrainingProgramme\Services\StudyPlanService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LearningProcessJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    private $learning_process;
    private $param;

    public function __construct(LearningProcessService $learning_process, array $param)
    {
        $this->learning_process = $learning_process;
        $this->param = $param;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->learning_process->insertLearningProcess($this->param);
    }
}
