<?php

namespace App\Providers;

use App\Services\Verification\DatabaseStudentVerificationProvider;
use App\Services\Verification\StudentVerificationProviderInterface;
use App\Services\Verification\StudentVerificationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            StudentVerificationProviderInterface::class,
            DatabaseStudentVerificationProvider::class
        );

        $this->app->singleton(StudentVerificationService::class, function ($app) {
            return new StudentVerificationService(
                $app->make(StudentVerificationProviderInterface::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
