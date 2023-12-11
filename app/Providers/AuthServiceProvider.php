<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;


class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('authorized-user-product', function (User $user, Product $post) {
            return $user->id === $post->user_id;
        });

        Gate::define('authorized-user-client', function (User $user, Client $client) {
            return $user->id === $client->user_id;
        });

        Gate::define('authorized-user-invoice', function (User $user, Invoice $invoice) {
            return $user->id === $invoice->user_id;
        });
    }
}
