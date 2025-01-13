<?php

namespace Kho8k\Crawler\Kho8kCrawlerFakeView;

use Kho8k\Core\Models\Movie;
use Illuminate\Support\Str;
use Kho8k\Core\Models\Actor;
use Kho8k\Core\Models\Category;
use Kho8k\Core\Models\Director;
use Kho8k\Core\Models\Episode;
use Kho8k\Core\Models\Region;
use Kho8k\Core\Models\Tag;
use Illuminate\Support\Facades\Log;

use Kho8k\Crawler\Kho8kCrawlerFakeView\Contracts\BaseCrawler;

class CrawlerO extends BaseCrawler
{
    public function handle()
    {
        $payload = json_decode($body = file_get_contents($this->link), true);

        $this->checkIsInExcludedList($payload);

        $movie = Movie::where('update_identity', $payload['movie']['_id'])->first();


        if (!$this->hasChange($movie, md5($body)) && $this->forceUpdate == false) {
            return false;
        }

        $info = (new Collector($payload, $this->fields, $this->forceUpdate))->get();

        if ($movie) {
            $movie->updated_at = now();
            $movie->update(collect($info)->only($this->fields)->merge(['update_checksum' => md5($body)])->toArray());
             // Nếu view_total chưa có giá trị, gán giá trị random
             if (is_null($movie->view_total)) {
                $movie->view_total = rand(100000, 500000);
                $movie->save();
            }
        } else {
            $movie = Movie::create(array_merge($info, [
                'update_handler' => static::class,
                'update_identity' => $payload['movie']['_id'],
                'update_checksum' => md5($body),
                'view_total' => rand(100000, 500000), // Thêm giá trị random cho view_total
            ]));
        }

        $this->syncActors($movie, $payload);
        $this->syncDirectors($movie, $payload);
        $this->syncCategories($movie, $payload);
        $this->syncRegions($movie, $payload);
        $this->syncTags($movie, $payload);
        $this->syncStudios($movie, $payload);
        $this->updateEpisodes($movie, $payload);
    }

    protected function hasChange(?Movie $movie, $checksum)
    {
        return is_null($movie) || ($movie->update_checksum != $checksum);
    }

    protected function checkIsInExcludedList($payload)
    {
        $newType = $payload['movie']['type'];
        if (in_array($newType, $this->excludedType)) {
            throw new \Exception("Thuộc định dạng đã loại trừ");
        }

        $newCategories = collect($payload['movie']['category'])->pluck('name')->toArray();
        if (array_intersect($newCategories, $this->excludedCategories)) {
            throw new \Exception("Thuộc thể loại đã loại trừ");
        }

        $newRegions = collect($payload['movie']['country'])->pluck('name')->toArray();
        if (array_intersect($newRegions, $this->excludedRegions)) {
            throw new \Exception("Thuộc quốc gia đã loại trừ");
        }
    }

    protected function syncActors($movie, array $payload)
    {
        if (!in_array('actors', $this->fields)) return;

        $actors = [];
        foreach ($payload['movie']['actor'] as $actor) {
            if (!trim($actor)) continue;
            $actors[] = Actor::firstOrCreate(['name' => trim($actor)])->id;
        }
        $movie->actors()->sync($actors);
    }

    protected function syncDirectors($movie, array $payload)
    {
        if (!in_array('directors', $this->fields)) return;

        $directors = [];
        foreach ($payload['movie']['director'] as $director) {
            if (!trim($director)) continue;
            $directors[] = Director::firstOrCreate(['name' => trim($director)])->id;
        }
        $movie->directors()->sync($directors);
    }

    protected function syncCategories($movie, array $payload)
    {
        if (!in_array('categories', $this->fields)) return;
        $categories = [];
        foreach ($payload['movie']['category'] as $category) {
            if (!trim($category['name'])) continue;
            $categories[] = Category::firstOrCreate(['name' => trim($category['name'])])->id;
        }
        if ($payload['movie']['type'] === 'hoathinh') $categories[] = Category::firstOrCreate(['name' => 'Hoạt Hình'])->id;
        if ($payload['movie']['type'] === 'tvshows') $categories[] = Category::firstOrCreate(['name' => 'TV Shows'])->id;
        $movie->categories()->sync($categories);
    }

    protected function syncRegions($movie, array $payload)
    {
        if (!in_array('regions', $this->fields)) return;

        $regions = [];
        foreach ($payload['movie']['country'] as $region) {
            if (!trim($region['name'])) continue;
            $regions[] = Region::firstOrCreate(['name' => trim($region['name'])])->id;
        }
        $movie->regions()->sync($regions);
    }

    protected function syncTags($movie, array $payload)
    {
        if (!in_array('tags', $this->fields)) return;

        $tags = [];
        $tags[] = Tag::firstOrCreate(['name' => trim($movie->name)])->id;
        $tags[] = Tag::firstOrCreate(['name' => trim($movie->origin_name)])->id;

        $movie->tags()->sync($tags);
    }

    protected function syncStudios($movie, array $payload)
    {
        if (!in_array('studios', $this->fields)) return;
    }

    protected function updateEpisodes($movie, $payload)
    {
        if (!in_array('episodes', $this->fields)) return;
        $flag = 0;
        foreach ($payload['episodes'] as $server) {
            foreach ($server['server_data'] as $episode) {
                if ($episode['link_m3u8']) {
                    Episode::updateOrCreate([
                        'id' => $movie->episodes[$flag]->id ?? null
                    ], [
                        'name' => $episode['name'],
                        'movie_id' => $movie->id,
                        'server' => $server['server_name'],
                        'type' => 'm3u8',
                        'link' => $episode['link_m3u8'],
                        'slug' => 'tap-' . Str::slug($episode['name'])
                    ]);
                    $flag++;
                }
                if ($episode['link_embed']) {
                    Episode::updateOrCreate([
                        'id' => $movie->episodes[$flag]->id ?? null
                    ], [
                        'name' => $episode['name'],
                        'movie_id' => $movie->id,
                        'server' => $server['server_name'],
                        'type' => 'embed',
                        'link' => $episode['link_embed'],
                        'slug' => 'tap-' . Str::slug($episode['name'])
                    ]);
                    $flag++;
                }
            }
        }
        for ($i = $flag; $i < count($movie->episodes); $i++) {
            $movie->episodes[$i]->delete();
        }
    }
}
