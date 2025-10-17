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

            // First update deadline statuses, then process them
            $schedule->command('deadlines:update-statuses')
                ->everyMinute()
                ->withoutOverlapping();

            // Run every minute to check for expired deadlines and deadlines about to expire
            $schedule->command('deadlines:process --type=all')
                ->everyMinute()
                ->withoutOverlapping()
                ->runInBackground();
        });
    }
}
