<?php

namespace App\Jobs;

use App\Services\NewsFetcherService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class FetchArticlesJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public $tries = 3;

    private NewsFetcherService $newsFetcherService;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->newsFetcherService = app(NewsFetcherService::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->newsFetcherService->fetchFromNewsApi();
            $this->newsFetcherService->fetchFromGuardian();
            $this->newsFetcherService->fetchFromNYTimes();
        } catch (\Exception $e) {
            Log::error("Failed to fetch articles from NY Times", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        Log::info("Articles fetched successfully.");
    }
}
