<?php

namespace App\Providers;

use App\Contract\Services\BookingServiceInterface;
use App\Services\BookingService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        App::bind(BookingServiceInterface::class, BookingService::class);
    }
}
