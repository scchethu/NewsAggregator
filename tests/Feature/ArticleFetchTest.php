<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\NewsCategory;
use App\Models\NewsSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ArticleFetchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the ArticleFetch command.
     *
     * @return void
     */
    public function test_article_fetch_command()
    {
        // Mock the HTTP response for NewsApi
        Http::fake([
            'newsdata.io/*' => Http::sequence()->push([
                'response' => [
                    'results' => [
                        [
                            'title' => 'Test Article 1',
                            'description' => 'Content of Test Article 1',
                            'pubDate' => '2024-01-01T00:00:00Z',
                            'category' => ['Tech'],
                            'source_name' => 'NewsAPI Source',
                        ],
                    ],
                ],
            ]),
            'content.guardianapis.com/*' => Http::sequence()->push([
                'response' => [
                    'results' => [
                        [
                            'webTitle' => 'Test Article 2',
                            'webPublicationDate' => '2024-01-02T00:00:00Z',
                            'sectionName' => 'World',
                            'pillarName' => 'News',
                        ],
                    ],
                ],
            ]),
        ]);

        // Run the command
        $this->artisan('app:article-fetch')->assertExitCode(0);

        // Assert the articles were created
        $this->assertCount(2, Article::all());

        // Assert the first article's data
        $article1 = Article::first();
        $this->assertEquals('Test Article 1', $article1->title);
        $this->assertEquals('Content of Test Article 1', $article1->content);
        $this->assertEquals('2024-01-01', $article1->published_at);
        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article 1',
            'content' => 'Content of Test Article 1',
        ]);

        // Assert the categories and sources were created
        $this->assertDatabaseHas('news_sources', [
            'name' => 'NewsAPI Source',
        ]);

        $this->assertDatabaseHas('news_categories', [
            'name' => 'Tech',
        ]);

        // Assert the second article's data
        $article2 = Article::orderBy('id', 'desc')->first(); // Assuming it was created last
        $this->assertEquals('Test Article 2', $article2->title);
        $this->assertDatabaseHas('articles', [
            'title' => 'Test Article 2',
            'content' => 'Test Article 2',
        ]);

        $this->assertDatabaseHas('news_sources', [
            'name' => 'News',
        ]);

        $this->assertDatabaseHas('news_categories', [
            'name' => 'World',
        ]);
    }
}
