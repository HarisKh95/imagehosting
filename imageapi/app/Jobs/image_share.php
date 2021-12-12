<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\image_mail;
use Illuminate\Support\Facades\Mail;

class image_share implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $details;
    public $mail;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($mail,$details)
    {
        $this->mail = $mail;
        $this->details = $details;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email = new image_mail($this->details);
        Mail::to($this->mail)->send($email);
    }
}
