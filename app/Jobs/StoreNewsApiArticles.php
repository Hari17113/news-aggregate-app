<?php

namespace App\Jobs;

use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StoreNewsApiArticles implements ShouldQueue
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
            $url = 'https://newsapi.org/v2/top-headlines/sources';
            $params = [
                'apiKey' => env('NEWS_API_KEY'),
                'language' => 'en'
            ];
            $response = Http::get($url, $params);

            if ($response->successful()) {
                $responseJson = collect($response->json());
                $sources = $responseJson['sources'];
                foreach ($sources as $source) {
                    DB::beginTransaction();
                    $sourceId = $source['id'];
                    $getArticlesUrl = 'https://newsapi.org/v2/everything';
                    $params = [
                        'apiKey' => env('NEWS_API_KEY'),
                        'sources' => $sourceId
                    ];
                    $articleResponse = Http::get($getArticlesUrl, $params);
                    if ($response->successful()) {
                        $articleResponseJson = $articleResponse->json();
                        collect($articleResponseJson['articles'])->chunk(500)->each(function ($articles) {
                            foreach ($articles as $article) {
                                Article::updateOrCreate(
                                    [
                                        'source' => $article['source']['id'],
                                        'author' => $article['author'],
                                        'title' => $article['title']
                                    ],
                                    [
                                        'content' => $article['content'],
                                        'category' => 'News-Api',
                                        'published_at' => Carbon::parse($article['publishedAt'])->format('Y-m-d H:i:s')
                                    ]
                                );
                            }
                        });
                        DB::commit();
                    } else {
                        $this->failed($response->json());
                    }
                }
            } else {
                $this->failed($response->json());
            }
        } catch (\Exception $e) {
            $this->failed($e->getMessage());
        }
    }

    /**
     * @param $response
     * @return void
     */
    public function failed($response)
    {
        DB::rollBack();
        Log::error('News API sync failed!!', ['response' => $response]);
    }
}
