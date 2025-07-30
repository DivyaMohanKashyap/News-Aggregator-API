<?php

namespace App\Providers;

use App\Jobs\FetchArticlesJob;
use App\Services\NewsFetcherService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class ScheduleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(Schedule $schedule): void
    {
        $schedule->job(new FetchArticlesJob())->daily();
    }
}
