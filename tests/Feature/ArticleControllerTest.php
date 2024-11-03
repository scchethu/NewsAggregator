<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\NewsCategory;
use App\Models\NewsSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a user and generate a token
        $this->user = \App\Models\User::factory()->create();
        $this->token = $this->user->createToken('TestToken')->plainTextToken;
    }

    /**
     * Test the index method to get a list of articles.
     *
     * @return void
     */
    public function test_index_returns_articles()
    {
        // Create some articles
        $source = NewsSource::create(['name' => 'Test Source']);
        $category = NewsCategory::create(['name' => 'Test Category']);

        Article::create([
            'title' => 'First Article',
            'content' => 'Content of the first article.',
            'published_at' => now(),
            'news_source_id' => $source->id,
        ])->categories()->attach($category->id);

        Article::create([
            'title' => 'Second Article',
            'content' => 'Content of the second article.',
            'published_at' => now(),
            'news_source_id' => $source->id,
        ])->categories()->attach($category->id);

        // Send GET request to the index endpoint with Bearer token
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->token,
        ])->get('/api/articles');

        // Assert response status and structure
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'content',
                        'published_at',
                        'categories' => [
                            '*' => ['id', 'name']
                        ],
                        'source' => ['id', 'name'],
                    ]
                ],
                'links',
            ]);

        // Assert that the correct number of articles is returned
        $this->assertCount(2, $response->json('data'));
    }

    /**
     * Test the show method to get a single article by ID.
     *
     * @return void
     */
    public function test_show_returns_article()
    {
        // Create an article
        $source = NewsSource::create(['name' => 'Test Source']);
        $category = NewsCategory::create(['name' => 'Test Category']);

        $article = Article::create([
            'title' => 'Test Article',
            'content' => 'Content of the test article.',
            'published_at' => now(),
            'news_source_id' => $source->id,
        ]);
        $article->categories()->attach($category->id);

        // Send GET request to the show endpoint with Bearer token
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->token,
        ])->get('/api/articles/' . $article->id);

        // Assert response status and structure
        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'title',
                'content',
                'published_at',
                'categories' => [
                    '*' => ['id', 'name']
                ],
                'source' => ['id', 'name'],
            ]);

        // Assert the article details are correct
        $this->assertEquals('Test Article', $response->json('title'));
    }

    /**
     * Test the show method returns 404 for a non-existent article.
     *
     * @return void
     */
    public function test_show_returns_not_found_for_non_existent_article()
    {
        // Send GET request to a non-existent article ID with Bearer token
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->token,
        ])->get('/api/articles/999'); // Assuming 999 doesn't exist

        // Assert response status is 404
        $response->assertStatus(404)
            ->assertJson(['message' => 'Article not found']);
    }
}
