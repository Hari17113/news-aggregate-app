<?php

namespace App\Jobs;

use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StoreTheGuardianNewsArticles implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $getArticlesUrl = 'https://content.guardianapis.com/search';
            $params = [
                'apiKey' => env('GUARDIAN_API_KEY'),
                'q' => 'debate',
                'tag' => 'politics/politics'
            ];
            $articleResponse = Http::get($getArticlesUrl, $params);
            if ($articleResponse->successful()) {
                $articlesJson = $articleResponse->json();
                collect($articlesJson['results'])->chunk(500)->each(function ($articlesChunk) {
                    DB::beginTransaction();
                    collect($articlesChunk)->each(function ($article) {
                        Article::updateOrCreate(
                            [
                                'source' => 'Guardian News Article',
                                'author' => $article['pillarId'],
                                'title' => $article['webTitle']
                            ],
                            [
                                'content' => $article['webUrl'],
                                'category' => $article['sectionId'],
                                'published_at' => Carbon::parse($article['webPublicationDate'])->format('Y-m-d H:i:s')
                            ]
                        );
                    });
                    DB::commit();
                });
            } else {
                $this->failed($articleResponse->json());
            }
        } catch (\Exception $exception) {
            $this->failed($exception->getMessage());
        }
    }

    /**
     * @param $response
     * @return void
     */
    public function failed($response)
    {
        DB::rollBack();
        Log::error('The Guardian News API sync failed!!', ['response' => $response]);
    }
}
