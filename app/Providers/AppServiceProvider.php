<?php

namespace App\Providers;

use App\Models\Detection;
use App\Policies\DetectionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('admin', function ($user) {
            return $user->isAdmin();
        });

        Gate::policy(Detection::class, DetectionPolicy::class);
    }
}