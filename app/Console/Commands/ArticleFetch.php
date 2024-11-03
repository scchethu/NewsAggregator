<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\NewsCategory;
use App\Models\NewsSource;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ArticleFetch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:article-fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        Article::flushQueryCache();
        NewsCategory::flushQueryCache();
        NewsSource::flushQueryCache();
        $news = [
            'NewsApi' => [
                'api' => 'https://newsdata.io/api/1/latest?apikey=pub_57976dae397619a260a368f6d4be58a8a0d79',
                'fields' => [
                    'title' => 'title',
                    'image_url' => 'image_url',
                    'content' => 'description',
                    'published_at' => 'pubDate',
                    'category' => 'category',
                    'news_source_id' => 'source_name'
                ]
            ],
            'guardianapis' => [
                'api' => 'https://content.guardianapis.com/search?page=1&&api-key=test',
                'fields' => [
                    'title' => 'webTitle',
                 //   'image_url' => 'image_url',
                    'content' => 'webTitle',
                    'published_at' => 'webPublicationDate',
                    'category' => 'sectionName',
                    'news_source_id' => 'pillarName'
                ]
            ],
        ];

        foreach ($news as $newsSource => $newItem) {
            $api_data = Http::get($newItem['api'])->json();

            if(isset($api_data['response'])){
                $api_data = $api_data['response'];
            }

            foreach ($api_data['results'] as $data) {
                $n = new Article();
                $n->news_type = $newsSource;
                $cat_ids = [];

                foreach ($newItem['fields'] as $key => $field) {
                    // Check if field exists in data before assigning
                    if (array_key_exists($field, $data)) {
                        if ($key !== 'category' && $key !== 'news_source_id' && $key !== 'published_at') {
                            $n->{$key} = $data[$field]??'';
                        }

                        if ($key === 'news_source_id') {
                            $source = NewsSource::updateOrCreate(
                                ['name' => $data[$field]],
                                ['name' => $data[$field]]
                            );
                            $n->{$key} = $source->id;
                        }
                        if($key=='published_at'){
                            $n->{$key} = Carbon::parse($data[$field])->toDateString();
                        }

                        if ($key === 'category') {
                            foreach (is_array($data[$field])?$data[$field]:[$data[$field]] as $category) {
                                $categoryModel = NewsCategory::updateOrCreate(
                                    ['name' => $category],
                                    ['name' => $category]
                                );
                                $cat_ids[] = $categoryModel->id;
                            }
                        }
                    }
                }

                // Save the article
                $n->save();

                // Sync the categories
                if (!empty($cat_ids)) {
                    $n->categories()->sync($cat_ids);
                }
            }
        }
    }

}
