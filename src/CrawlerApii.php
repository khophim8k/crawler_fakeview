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


class CrawlerApii extends BaseCrawler
{
    public function handle()
    {
        $payload = json_decode($body = file_get_contents($this->link), true);
        // In ra thông tin payload để kiểm tra
       
        $this->checkIsInExcludedList($payload);

        $movie = Movie::where('update_identity', $payload['data'][0]['id'])->first();


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
                'update_identity' => $payload['data'][0]['id'],
                'update_checksum' => md5($body),
                'view_total' => rand(100000, 500000), // Thêm giá trị random cho view_total
            ]));
        }

        $this->syncActors($movie, $payload);
     
        $this->syncCategories($movie, $payload);
        // $this->syncRegions($movie, $payload);
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
       

        $newCategories = [];
        if (isset($payload['data']['categories']['name']) && is_array($payload['data']['categories']['name'])) {
            $newCategories = $payload['data']['categories']['name'];
        }
        if (array_intersect($newCategories, $this->excludedCategories)) {
            throw new \Exception("Thuộc thể loại đã loại trừ");
        }

   
            $newRegions = [];
            if (!empty($payload['data']['country'])) {
                $newRegions = [$payload['data']['country']];
            }
        
            if (array_intersect($newRegions, $this->excludedRegions)) {
                throw new \Exception("Thuộc quốc gia đã loại trừ");
            }
    }

    protected function syncActors($movie, array $payload)
    {
        if (!in_array('actors', $this->fields)) return;

        $actors = [];
        if (!empty($payload['data'][0]['actors']) && is_array($payload['data'][0]['actors'])) {
            foreach ($payload['data'][0]['actors'] as $actor) {
                if (!trim($actor)) continue;
                $actors[] = Actor::firstOrCreate(['name' => trim($actor)])->id;
            }
        }
        $movie->actors()->sync($actors);
    }

    protected function syncDirectors($movie, array $payload)
    {
        if (!in_array('directors', $this->fields)) return;

        $directors = [];
        foreach ($payload['data'][0]['director'] as $director) {
            if (!trim($director)) continue;
            $directors[] = Director::firstOrCreate(['name' => trim($director)])->id;
        }
        $movie->directors()->sync($directors);
    }

    protected function syncCategories($movie, array $payload)
    {
      
        $categories = [];
            if (!empty($payload['data'][0]['categories']['name'])) {
                foreach ($payload['data'][0]['categories']['name'] as $category) {
                    if (!trim($category)) continue;
                    $categories[] = Category::firstOrCreate(['name' => trim($category)])->id;
                }
            } else {
                $categories[] = Category::firstOrCreate(['name' => 'Việt Nam Clip'])->id;
            }
    
        $movie->categories()->sync($categories);
    }

    protected function syncRegions($movie, array $payload)
    {
        if (!in_array('regions', $this->fields)) return;

        $regions = [];
        foreach ($payload['data'][0]['country'] as $region) {
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
       
        $episodes = $payload['data'][0]['episodes'];
        if (!empty($episodes) && is_array($episodes)) {
            // Kiểm tra nếu episodes là một mảng đơn giản chứa các URL
            if (isset($episodes[0]) && is_string($episodes[0])) {
                Episode::updateOrCreate([
                    'id' => $movie->episodes[0]->id ?? null
                ], [
                    'name' => 'vip',
                    'movie_id' => $movie->id,
                    'server' => 'vip',
                    'type' => 'm3u8',
                    'link' => $episodes[0],
                    'slug' => 'vip'
                ]);
            } else {
                // Xử lý trường hợp episodes là mảng phức tạp
                foreach ($episodes as $server) {
                    if (!empty($server)) {
                        Episode::updateOrCreate([
                            'id' => $movie->episodes[0]->id ?? null
                        ], [
                            'name' => 'vip',
                            'movie_id' => $movie->id,
                            'server' => 'vip',
                            'type' => 'm3u8',
                            'link' => is_array($server) ? $server[0] : $server,
                            'slug' => 'vip'
                        ]);
                        break; // Chỉ lấy tập đầu tiên
                    }
                }
            }
        }
        
        
       
    }
}