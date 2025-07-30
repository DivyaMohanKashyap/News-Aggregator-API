<?php

namespace App\Console\Commands;

use App\Jobs\FetchArticlesJob;
use App\Models\Article;
use App\Services\NewsFetcherService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SourceArticleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to source articles from news api platforms.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Fetching articles...");

        try {
            if (FetchArticlesJob::dispatch()) {
                $this->info("Articles fetched successfully.");
            } else {
                throw new \Exception("Failed to dispatch the FetchArticlesJob.");
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 0, $e);
        }

        $this->info("Command processed successfully.");
    }
}
