<?php

use Illuminate\Support\Facades\Route;
use App\Models\Article;
use GuzzleHttp\Client;

Route::get('/news', function () {
    $client = new Client(['base_uri' => 'https://newsapi.org/v2/']);
    $apiKey = env('NEWSAPI_KEY');

    $response = $client->get('top-headlines', [
        'query' => [
            'country' => 'us',
            'category' => 'technology',
            'pageSize' => 5,
            'apiKey' => $apiKey
        ]
    ]);

    $news = json_decode($response->getBody(), true);

    dd($news);
});
Route::get('/', function () {
    return view('welcome');
});
