<?php

namespace App\Jobs;

use App\Http\Domain\Finance\Services\TransactionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class TransactionJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $attributes;
    public function __construct($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $transaction = app()->service(TransactionService::class)->insertData($this->attributes);
    }
    
    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        // var_dump($exception->getMessage());
    }
}
