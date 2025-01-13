<?php

namespace Kho8k\Crawler\Kho8kCrawlerFakeView\Controllers;


use Backpack\CRUD\app\Http\Controllers\CrudController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Kho8k\Crawler\Kho8kCrawlerFakeView\CrawlernguonC;
use Kho8k\Core\Models\Movie;

/**
 * Class CrawlController
 * @package Kho8k\Crawler\Kho8kCrawlerFakeView\Controllers
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class CrawlnguonCController extends CrudController
{
    // Fetch Nguonc
    public function fetch(Request $request)
    {
        try {
            $data = collect();
            if (preg_match('/(.*?)(\/film\/)(.*?)/', $request['link'])) {
                $response = json_decode(file_get_contents($request['link']), true);
                $data->push(collect($response['movie'])->only('name', 'slug')->toArray());
            } else {
                for ($i = $request['from']; $i <= $request['to']; $i++) {
                    $response = json_decode(Http::timeout(30)->get($request['link'], [
                        'page' => $i
                    ]), true);
                    if ($response['status']) {

                        $data->push(...$response['items']);
                    }
                }
            }


            return $data->shuffle();
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    // End Fetch Nguonc

    // ShowCrawPage Nguonc
    public function showCrawlPage(Request $request)
    {
        $categories = [];
        $regions = [];
        try {
            Cache::forget('nguonc_categories');
            Cache::forget('nguonc_regions');
            $categories = Cache::remember('nguonc_categories', 86400, function () {
                $data = json_decode('[
    {
        "name": "Hành Động"
    },
    {
        "name": "Phiêu Lưu"
    },
    {
        "name": "Hoạt Hình"
    },
    {
        "name": "Hài"
    },
    {
        "name": "Hình Sự"
    },
    {
        "name": "Tài Liệu"
    },
    {
        "name": "Chính Kịch"
    },
    {
        "name": "Gia Đình"
    },
    {
        "name": "Giả Tưởng"
    },
    {
        "name": "Lịch Sử"
    },
    {
        "name": "Kinh Dị"
    },
    {
        "name": "Nhạc"
    },
    {
        "name": "Bí Ẩn"
    },
    {
        "name": "Lãng Mạn"
    },
    {
        "name": "Khoa Học Viễn Tưởng"
    },
    {
        "name": "Gây Cấn"
    },
    {
        "name": "Chiến Tranh"
    },
    {
        "name": "Tâm Lý"
    },
    {
        "name": "Tình Cảm"
    },
    {
        "name": "Cổ Trang"
    },
    {
        "name": "Miền Tây"
    },
    {
        "name": "Phim 18+"
    }
]', true) ?? [];
                return collect($data)->pluck('name', 'name')->toArray();
            });

            $regions = Cache::remember('nguonc_regions', 86400, function () {
                $data = json_decode('[
    {
        "name": "Âu Mỹ"
    },
    {
        "name": "Anh"
    },
    {
        "name": "Trung Quốc"
    },
    {
        "name": "Indonesia"
    },
    {
        "name": "Việt Nam"
    },
    {
        "name": "Pháp"
    },
    {
        "name": "Hồng Kông"
    },
    {
        "name": "Hàn Quốc"
    },
    {
        "name": "Nhật Bản"
    },
    {
        "name": "Thái Lan"
    },
    {
        "name": "Đài Loan"
    },
    {
        "name": "Nga"
    },
    {
        "name": "Hà Lan"
    },
    {
        "name": "Philippines"
    },
    {
        "name": "Ấn Độ"
    }
]', true) ?? [];
                return collect($data)->pluck('name', 'name')->toArray();
            });
        } catch (\Throwable $th) {
        }

        $fields = $this->movieUpdateOptions();

        return view('khophim8k-crawler-fakerview::crawlc', compact('fields', 'regions', 'categories'));
    }
    // End ShowCrawlPage Nguonc



    // Crawl Nguonc
    public function crawl(Request $request)
    {
        $pattern = sprintf('%s/api/film/{slug}', 'https://phim.nguonc.com');
        try {
            $link = str_replace('{slug}', $request['slug'], $pattern);
            $crawler = (new CrawlernguonC($link, request('fields', []), request('excludedCategories', []), request('excludedRegions', []), request('excludedType', []), request('forceUpdate', false)))->handle();
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'wait' => false], 500);
        }
        return response()->json(['message' => $crawler ? 'OK' : "Updated", 'wait' => $crawler ?? true]);
    }
    // End Crawl Nguonc


    protected function movieUpdateOptions(): array
    {
        return [
            'Tiến độ phim' => [
                'episodes' => 'Tập mới',
                'status' => 'Trạng thái phim',
                'episode_time' => 'Thời lượng tập phim',
                'episode_current' => 'Số tập phim hiện tại',
                'episode_total' => 'Tổng số tập phim',
            ],
            'Thông tin phim' => [
                'name' => 'Tên phim',
                'origin_name' => 'Tên gốc phim',
                'content' => 'Mô tả nội dung phim',
                'thumb_url' => 'Ảnh Thumb',
                'poster_url' => 'Ảnh Poster',
                'trailer_url' => 'Trailer URL',
                'quality' => 'Chất lượng phim',
                'language' => 'Ngôn ngữ',
                'notify' => 'Nội dung thông báo',
                'showtimes' => 'Giờ chiếu phim',
                'publish_year' => 'Năm xuất bản',
                'is_copyright' => 'Đánh dấu có bản quyền',
            ],
            'Phân loại' => [
                'type' => 'Định dạng phim',
                'is_shown_in_theater' => 'Đánh dấu phim chiếu rạp',
                'actors' => 'Diễn viên',
                'directors' => 'Đạo diễn',
                'categories' => 'Thể loại',
                'regions' => 'Khu vực',
                'tags' => 'Từ khóa',
                'studios' => 'Studio',
            ]
        ];
    }

    public function getMoviesFromParams(Request $request)
    {
        $field = explode('-', request('params'))[0];
        $val = explode('-', request('params'))[1];
        if (!$val) {
            return Movie::where($field, $val)->orWhere($field, 'like', '%.com%')->orWhere($field, NULL)->get();
        } else {
            return Movie::where($field, $val)->get();
        }
    }
}
