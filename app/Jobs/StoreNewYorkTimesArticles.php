<?php

namespace App\Jobs;

use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StoreNewYorkTimesArticles implements ShouldQueue
{
    use Queueable;

    protected const NEW_YORK_TIMES_SECTIONS = ['home', 'arts', 'automobiles', 'books/review', 'business', 'fashion', 'food', 'health', 'home', 'insider', 'magazine', 'movies', 'nyregion', 'obituaries', 'opinion', 'politics', 'realestate', 'science', 'sports', 'sundayreview', 'technology', 'theater', 't-magazine', 'travel', 'upshot', 'us', 'world'];
    protected const URL = "https://api.nytimes.com/svc/topstories/v2";


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
            $params = [
                'api-key' => env('NY_TIMES_API_KEY'),
            ];
            foreach (self::NEW_YORK_TIMES_SECTIONS as $section) {
                DB::beginTransaction();
                $articleResponse = Http::get(self::URL . "/$section.json", $params);
                if ($articleResponse->successful()) {
                    $articleJsonResponse = $articleResponse->json();
                    collect($articleJsonResponse['results'])->chunk('500')->each(function ($articlesChunk) use ($section) {
                        foreach ($articlesChunk as $article) {
                            Article::updateOrCreate(
                                [
                                    'source' => 'NewYorkTimesArticles',
                                    'author' => $article['byline'],
                                    'title' => $article['title']
                                ],
                                [
                                    'content' => $article['abstract'],
                                    'category' => $section,
                                    'published_at' => Carbon::parse($article['published_date'])->format('Y-m-d H:i:s')
                                ]
                            );
                        }
                    });
                } else {
                    $this->failed($articleResponse);
                }
                DB::commit();
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
        Log::error('NewYork News API sync failed!!', ['response' => $response]);
    }
}
