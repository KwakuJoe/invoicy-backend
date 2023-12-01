<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\PasswordResetSuccessNotifier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class PasswordResetSuccessJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

     public User $user;
    public function __construct(User $user)
    {
        $this->user = $user;
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
        $this->user->notify(new PasswordResetSuccessNotifier($this->user));
    }

      /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        // Send user notification of failure, etc...
    }

}
