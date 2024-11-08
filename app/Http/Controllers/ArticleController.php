<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

/**
 * Class ArticleController
 * @package App\Http\Controllers
 */
class ArticleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/articles",
     *     tags={"Articles"},
     *     summary="Get a list of articles",
     *     description="Retrieve a list of articles with optional filtering by keyword, date, category, or source.",
     *     @OA\Parameter(
     *         name="keyword",
     *         in="query",
     *         required=false,
     *         description="Keyword to filter articles by title or content",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="date",
     *         in="query",
     *         required=false,
     *         description="Filter articles by publication date (YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         required=false,
     *         description="Filter articles by category name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="source",
     *         in="query",
     *         required=false,
     *         description="Filter articles by source name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of articles retrieved successfully",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="data"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=404, description="No articles found"),
     * )
     */
    public function index(Request $request)
    {
        $query = Article::query()->with(['categories', 'source']);

        // Filtering by keyword
        if ($request->has('keyword')) {
            $query->where('title', 'like', '%' . $request->keyword . '%')
                ->orWhere('content', 'like', '%' . $request->keyword . '%');
        }

        // Filtering by date
        if ($request->has('date')) {
            $query->whereDate('published_at', $request->date);
        }

        // Filtering by category
        if ($request->has('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('name', $request->category);
            });
        }

        // Filtering by source
        if ($request->has('source')) {
            $query->whereHas('source', function ($q) use ($request) {
                $q->where('name', $request->source);
            });
        }

        // Pagination
        $articles = $query->paginate(10); // Adjust per page as necessary

        return response()->json($articles);
    }

    /**
     * @OA\Get(
     *     path="/api/articles/{id}",
     *     tags={"Articles"},
     *     summary="Get a single article by ID",
     *     description="Retrieve the details of a specific article by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the article to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Article details retrieved successfully"
     *     ),
     *     @OA\Response(response=404, description="Article not found"),
     * )
     */
    public function show($id)
    {
        $article = Article::where('id', $id)->with(['categories', 'source'])->first();

        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }

        return response()->json($article);
    }
}
