<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use App\Http\Domain\Student\Services\LearningProcessService;
use App\Http\Domain\TrainingProgramme\Services\StudyPlanService;
use App\Jobs\LearningProcessJob;

class LearningProcessCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:LearningProcess';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "";

    /**
     * client
     *
     * @var mixed
     */
    private $client;


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        try {
            app()->configAuth();
            auth()->fakeDefaultsGuard('cmd');
            $service = app()->service(LearningProcessService::class);
            $response = $service->store();
            $this->info('Successfully!');

            // $study_plan_service = app()->service(StudyPlanService::class);
            // $param = $study_plan_service->getParams2();
            // $learning_process_service = app()->service(LearningProcessService::class);
            // foreach ($param as $param) {
            //     dispatch(new LearningProcessJob($learning_process_service, $param))->onQueue('learning_process');
            //     $this->info("Successfully Dispatch");
            // }
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }
}
