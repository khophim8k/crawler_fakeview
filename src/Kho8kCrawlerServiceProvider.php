<?php

namespace Kho8k\Crawler\Kho8kCrawler;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as SP;
use Kho8k\Crawler\Kho8kCrawler\Console\CrawlerScheduleCommand;
use Kho8k\Crawler\Kho8kCrawler\Option;

class Kho8kCrawlerServiceProvider extends SP
{
    /**
     * Get the policies defined on the provider.
     *
     * @return array
     */
    public function policies()
    {
        return [];
    }

    public function register()
    {

        config(['plugins' => array_merge(config('plugins', []), [
            'khophim8k/khophim8k-crawler' =>
            [
                'name' => 'Phim8k Crawler v2',
                'package_name' => 'khophim8k/khophim8k-crawler',
                'icon' => 'lab la-grav',
                'entries' => [
                    ['name' => 'Xvnapi Crawler', 'icon' => 'lab la-cloudscale', 'url' => backpack_url('/plugin/khophim8k-crawler')],
                    ['name' => 'Apii Crawler', 'icon' => 'lab la-cloudscale', 'url' => backpack_url('/plugin/apii-crawler')],
                    ['name' => 'Kkphim Crawler', 'icon' => 'lab la-cloudscale', 'url' => backpack_url('/plugin/kkphim-crawler')],
                    ['name' => 'Ophim Crawler', 'icon' => 'lab la-cloudscale', 'url' => backpack_url('/plugin/ophim-crawler')],
                    ['name' => 'NguonC Crawler', 'icon' => 'lab la-cloudscale', 'url' => backpack_url('/plugin/nguonc-crawler')],
                    ['name' => 'Option', 'icon' => 'la la-cog', 'url' => backpack_url('/plugin/khophim8k-crawler/options')],
                ],
            ]
        ])]);

        config(['logging.channels' => array_merge(config('logging.channels', []), [
            'khophim8k-crawler' => [
                'driver' => 'daily',
                'path' => storage_path('logs/khophim8k/khophim8k-crawler.log'),
                'level' => env('LOG_LEVEL', 'debug'),
                'days' => 7,
            ],
        ])]);

        config(['kho8k.updaters' => array_merge(config('kho8k.updaters', []), [
            [
                'name' => 'Kho8k Crawler',
                'handler' => 'Kho8k\Crawler\Kho8kCrawler\Crawler'
            ]
        ])]);
    }

    public function boot()
    {
        $this->commands([
            CrawlerScheduleCommand::class,
        ]);

        $this->app->booted(function () {
            $this->loadScheduler();
        });

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'khophim8k-crawler');
    }

    protected function loadScheduler()
    {
        $schedule = $this->app->make(Schedule::class);
        $schedule->command('kho8k:plugins:khophim8k-crawler:schedule')->cron(Option::get('crawler_schedule_cron_config', '*/10 * * * *'))->withoutOverlapping();
    }
}
