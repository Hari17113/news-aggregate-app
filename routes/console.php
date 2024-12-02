<?php

use App\Jobs\StoreNewsApiArticles;
use App\Jobs\StoreNewYorkTimesArticles;
use App\Jobs\StoreTheGuardianNewsArticles;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::job(new StoreNewsApiArticles)->daily();
Schedule::job(new StoreNewYorkTimesArticles)->daily();
Schedule::job(new StoreTheGuardianNewsArticles)->daily();
