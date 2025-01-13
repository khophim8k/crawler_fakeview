<?php

use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'Kho8k\Crawler\Kho8kCrawlerFakeView\Controllers',   
], function () {
    Route::get('/plugin/khophim8k-crawler-fakerview', 'CrawlController@showCrawlPage');
    Route::get('/plugin/khophim8k-crawler-fakerview/options', 'CrawlerSettingController@editOptions');
    Route::put('/plugin/khophim8k-crawler-fakerview/options', 'CrawlerSettingController@updateOptions');
    Route::get('/plugin/khophim8k-crawler-fakerview/fetch', 'CrawlController@fetch');
    Route::post('/plugin/khophim8k-crawler-fakerview/crawl', 'CrawlController@crawl');
    Route::post('/plugin/khophim8k-crawler-fakerview/get-all-movies', 'CrawlController@getAllMovies');
    Route::post('/plugin/khophim8k-crawler-fakerview/get-movies', 'CrawlController@getMoviesFromParams');

    Route::get('/plugin/kkphim-crawler', 'CrawlkkController@showCrawlPage');
    Route::get('/plugin/kkphim-crawler/options', 'CrawlerSettingController@editOptions');
    Route::put('/plugin/kkphim-crawler/options', 'CrawlerSettingController@updateOptions');
    Route::get('/plugin/kkphim-crawler/fetch', 'CrawlkkController@fetch');
    Route::post('/plugin/kkphim-crawler/crawl', 'CrawlkkController@crawl');
    Route::post('/plugin/kkphim-crawler/get-all-movies', 'CrawlkkController@getAllMovies');
    Route::post('/plugin/kkphim-crawler/get-movies', 'CrawlkkController@getMoviesFromParams');

    Route::get('/plugin/ophim-crawler', 'CrawlOController@showCrawlPage');
    Route::get('/plugin/ophim-crawler/options', 'CrawlerSettingController@editOptions');
    Route::put('/plugin/ophim-crawler/options', 'CrawlerSettingController@updateOptions');
    Route::get('/plugin/ophim-crawler/fetch', 'CrawlOController@fetch');
    Route::post('/plugin/ophim-crawler/crawl', 'CrawlOController@crawl');
    Route::post('/plugin/ophim-crawler/get-all-movies', 'CrawlOController@getAllMovies');
    Route::post('/plugin/ophim-crawler/get-movies', 'CrawlOController@getMoviesFromParams');

    Route::get('/plugin/apii-crawler', 'CrawlApiiController@showCrawlPage');
    Route::get('/plugin/apii-crawler/options', 'CrawlerSettingController@editOptions');
    Route::put('/plugin/apii-crawler/options', 'CrawlerSettingController@updateOptions');
    Route::get('/plugin/apii-crawler/fetch', 'CrawlApiiController@fetch');
    Route::post('/plugin/apii-crawler/crawl', 'CrawlApiiController@crawl');
    Route::post('/plugin/apii-crawler/get-all-movies', 'CrawlApiiController@getAllMovies');
    Route::post('/plugin/apii-crawler/get-movies', 'CrawlApiiController@getMoviesFromParams');

    Route::get('/plugin/nguonc-crawler', 'CrawlnguonCController@showCrawlPage');
    Route::get('/plugin/nguonc-crawler/options', 'CrawlerSettingController@editOptions');
    Route::put('/plugin/nguonc-crawler/options', 'CrawlerSettingController@updateOptions');
    Route::get('/plugin/nguonc-crawler/fetch', 'CrawlnguonCController@fetch');
    Route::post('/plugin/nguonc-crawler/crawl', 'CrawlnguonCController@crawl');
    Route::post('/plugin/nguonc-crawler/get-all-movies', 'CrawlnguonCController@getAllMovies');
    Route::post('/plugin/nguonc-crawler/get-movies', 'CrawlnguonCController@getMoviesFromParams');
});
