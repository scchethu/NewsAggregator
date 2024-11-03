<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\NewsCategory;
use App\Models\NewsSource;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class UserPreferenceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a user and generate a token
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('TestToken')->plainTextToken;

        // Create sample data
        $this->newsSource = NewsSource::create(['name' => 'Test Source']);
        $this->category = NewsCategory::create(['name' => 'Test Category']);
        Article::create([
            'title' => 'Sample Article',
            'content' => 'Sample content',
            'author' => 'Sample Author',
            'news_source_id' => $this->newsSource->id,
        ]);
    }

    /**
     * Test setting user preferences.
     *
     * @return void
     */
    public function test_set_user_preferences()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->postJson('/api/preferences', [
                'news_source_id' => $this->newsSource->id,
                'news_category_id' => $this->category->id,
                'author' => 'Sample Author',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user_id',
                'news_source_id',
                'news_category_id',
                'author',
            ]);

        // Verify the preferences are stored
        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $this->user->id,
            'news_source_id' => $this->newsSource->id,
            'news_category_id' => $this->category->id,
            'author' => 'Sample Author',
        ]);
    }

    /**
     * Test setting user preferences with invalid input.
     *
     * @return void
     */
    public function test_set_user_preferences_invalid_input()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->postJson('/api/preferences', [
                'news_source_id' => 999, // Invalid ID
                'news_category_id' => 999, // Invalid ID
                'author' => 'Sample Author',
            ]);

        $response->assertStatus(422);
    }

    /**
     * Test getting all authors.
     *
     * @return void
     */
    public function test_get_authors()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->get('/api/authors');

        $response->assertStatus(200)
            ->assertJsonFragment(['Sample Author']);
    }

    /**
     * Test getting all categories.
     *
     * @return void
     */
    public function test_get_categories()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->get('/api/categories');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Test Category']);
    }

    /**
     * Test getting all news sources.
     *
     * @return void
     */
    public function test_get_news_sources()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->get('/api/news-sources');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Test Source']);
    }

    /**
     * Test getting personalized feed based on user preferences.
     *
     * @return void
     */
    public function test_get_personalized_feed()
    {
        UserPreference::create([
            'user_id' => $this->user->id,
            'news_source_id' => $this->newsSource->id,
            'news_category_id' => $this->category->id,
            'author' => 'Sample Author',
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
            ->get('/api/personalized-feed');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'title',
                    'content',
                    'author',
                    'news_source_id',
                ]
            ]);
    }
}
