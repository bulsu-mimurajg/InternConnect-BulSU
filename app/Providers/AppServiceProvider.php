<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

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
        // Schedule automatic deadline processing
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            
            // Run every hour to check for expired deadlines
            $schedule->command('deadlines:process --type=all')
                ->hourly()
                ->withoutOverlapping()
                ->runInBackground();
        });
    }
}
