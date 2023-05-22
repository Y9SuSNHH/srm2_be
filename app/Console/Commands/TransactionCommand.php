<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Domain\Finance\Services\TransactionService;
use App\Jobs\StudentReceivable;
use App\Jobs\TransactionJob;

class TransactionCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:CreateStudentCredit';

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
            $transaction = app()->service(TransactionService::class);
            $data = $transaction->dataInsert();
            $bar = $this->output->createProgressBar(count($data));
            $bar->start();
            foreach ($data as $val) {
                dispatch(new TransactionJob($val))->onQueue('transaction');
                $bar->advance();
            }
            dispatch(new StudentReceivable())->onQueue('student_receivable');
            $bar->finish();
            $this->info("");
            $this->info("Successfully Dispatch");
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }
}
