<?php

namespace App\Jobs;

use App\Mail\SendInvoiceMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $invoice;
    public $url;

    public $emailSubject;

    public function __construct($invoice, $url, $emailSubject)
    {
        $this->invoice = $invoice;
        $this->url = $url;
        $this->emailSubject = $emailSubject;
    }

     /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 3;
    public $tries = 3;


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // get the reciepient

        foreach ([$this->invoice->client_email] as $recipient) {
            Mail::to($recipient)->send(new SendInvoiceMail($this->invoice, $this->url, $this->emailSubject));
        }
    }
}
