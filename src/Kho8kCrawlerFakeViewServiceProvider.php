<?php

namespace Kho8k\Crawler\Kho8kCrawlerFakeView;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as SP;
use Kho8k\Crawler\Kho8kCrawlerFakeView\Console\CrawlerScheduleCommand;
use Kho8k\Crawler\Kho8kCrawlerFakeView\Option;

class Kho8kCrawlerFakeViewServiceProvider extends SP
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
            'khophim8k/khophim8k-crawler-fakerview' =>
            [
                'name' => 'Phim8k Crawler Faker View nè',
                'package_name' => 'khophim8k/khophim8k-crawler-fakerview',
                'icon' => 'lab la-grav',
                'entries' => [
                    ['name' => 'Xvnapi Crawler', 'icon' => 'lab la-cloudscale', 'url' => backpack_url('/plugin/khophim8k-crawler-fakerview')],
                    ['name' => 'Kho8k Crawler', 'icon' => 'lab la-cloudscale', 'url' => backpack_url('/plugin/apii-crawler')],
                    ['name' => 'Kkphim Crawler', 'icon' => 'lab la-cloudscale', 'url' => backpack_url('/plugin/kkphim-crawler')],
                    ['name' => 'Ophim Crawler', 'icon' => 'lab la-cloudscale', 'url' => backpack_url('/plugin/ophim-crawler')],
                    ['name' => 'NguonC Crawler', 'icon' => 'lab la-cloudscale', 'url' => backpack_url('/plugin/nguonc-crawler')],
                    ['name' => 'Option', 'icon' => 'la la-cog', 'url' => backpack_url('/plugin/khophim8k-crawler-fakerview/options')],
                ],
            ]
        ])]);

        config(['logging.channels' => array_merge(config('logging.channels', []), [
            'khophim8k-crawler-fakerview' => [
                'driver' => 'daily',
                'path' => storage_path('logs/khophim8k/khophim8k-crawler-fakerview.log'),
                'level' => env('LOG_LEVEL', 'debug'),
                'days' => 7,
            ],
        ])]);

        config(['kho8k.updaters' => array_merge(config('kho8k.updaters', []), [
            [
                'name' => 'Kho8k Crawler',
                'handler' => 'Kho8k\Crawler\Kho8kCrawlerFakeView\Crawler'
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
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'khophim8k-crawler-fakerview');
    }

    protected function loadScheduler()
    {
        $schedule = $this->app->make(Schedule::class);
        $schedule->command('kho8k:plugins:khophim8k-crawler-fakerview:schedule')->cron(Option::get('crawler_schedule_cron_config', '*/10 * * * *'))->withoutOverlapping();
    }
}
