<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\NewsCategory;
use App\Models\NewsSource;
use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPreferenceController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/preferences",
     *     tags={"User Preferences"},
     *     summary="Set user preferences",
     *     description="Set the user's news source, category, and author preferences.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"news_source_id", "news_category_id", "author"},
     *             @OA\Property(property="news_source_id", type="integer", description="ID of the preferred news source"),
     *             @OA\Property(property="news_category_id", type="integer", description="ID of the preferred news category"),
     *             @OA\Property(property="author", type="string", description="Preferred author name")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Preferences set successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(response=400, description="Invalid input")
     * )
     */
    public function setPreference(Request $request)
    {
        $request->validate([
            'news_source_id' => 'nullable|exists:news_sources,id',
            'news_category_id' => 'nullable|exists:news_categories,id',
            'author' => 'nullable|string',
        ]);

        $user = Auth::user();
        $preference = UserPreference::updateOrCreate(
            ['user_id' => $user->id],
            $request->only(['news_source_id', 'news_category_id', 'author'])
        );

        return response()->json($preference, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/authors",
     *     tags={"User Preferences"},
     *     summary="Get all authors",
     *     description="Retrieve a list of unique authors from articles.",
     *     @OA\Response(response=200, description="List of authors",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function getAuthors()
    {
        $authors = Article::distinct('author')->get()->pluck('author');
        return response()->json($authors);
    }

    /**
     * @OA\Get(
     *     path="/api/categories",
     *     tags={"User Preferences"},
     *     summary="Get all categories",
     *     description="Retrieve a list of all news categories.",
     *     @OA\Response(response=200, description="List of categories",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function getCategories()
    {
        $categories = NewsCategory::all();
        return response()->json($categories);
    }

    /**
     * @OA\Get(
     *     path="/api/news-sources",
     *     tags={"User Preferences"},
     *     summary="Get all news sources",
     *     description="Retrieve a list of all news sources.",
     *     @OA\Response(response=200, description="List of news sources",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function getNewsSources()
    {
        $newsSources = NewsSource::all();
        return response()->json($newsSources);
    }

    /**
     * @OA\Get(
     *     path="/api/personalized-feed",
     *     tags={"User Preferences"},
     *     summary="Get personalized news feed",
     *     description="Retrieve a personalized news feed based on user preferences.",
     *     @OA\Response(response=200, description="List of personalized articles",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function getPersonalizedFeed()
    {
        $user = Auth::user();
        $preference = UserPreference::where('user_id', $user->id)->first();

        // Fetch articles based on user preferences
        $query = Article::query()->with(['categories', 'source']);

        if ($preference) {
            if ($preference->news_source_id) {
                $query->where('news_source_id', $preference->news_source_id);
            }
            if ($preference->news_category_id) {
                $query->whereHas('categories', function ($q) use ($preference) {
                    $q->where('news_category_id', $preference->news_category_id);
                });
            }
            if ($preference->author) {
                $query->where('author', $preference->author);
            }
        }

        $articles = $query->get();
        return response()->json($articles);
    }
}
