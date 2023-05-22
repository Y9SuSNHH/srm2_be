<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;

class SendEmailJob implements ShouldQueue
{
    private $mail;

    public function __construct($email)
    {
        $this->mail = $email;
    }

    public function handle()
    {
        #todo
    }

}
