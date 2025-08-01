<?php

namespace App\Providers;

use App\Jobs\FetchArticlesJob;
use App\Models\Article;
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
        $schedule->job(new FetchArticlesJob(Article::SOURCE_NEWS_API))->daily();
        $schedule->job(new FetchArticlesJob(Article::SOURCE_NYTIMES))->fridays()->at('12:00');
        $schedule->job(new FetchArticlesJob(Article::SOURCE_THE_GUARDIAN))->mondays()->at('08:00');
    }
}
