<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleListRequest;
use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    protected const pagination = 10;

    /**
     * @param ArticleListRequest $request
     * @return JsonResponse
     */
    public function lists(ArticleListRequest $request)
    {
        $articles = Article::when($request->has('keyword'), function ($query) use ($request) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($request, $keyword) {
                $q->where('title', 'like', '%' . $keyword . '%')
                    ->orWhere('content', 'like', '%' . $keyword . '%');
            });
        })
        ->when($request->has('category'), function ($query) use ($request) {
            $query->where('category', $request->input('category'));
        })
        ->when($request->has('source'), function ($query) use ($request) {
            $query->where('source', $request->input('source'));
        })
        ->when($request->has('date'), function ($query) use ($request) {
            $query->whereDate('published_at', $request->input('date'));
        })->paginate(self::pagination);

        return response()->json([
            'data' => $articles->items(),
            'pagination' => [
                'current_page' => $articles->currentPage(),
                'total_pages' => ceil($articles->total() / self::pagination),
                'per_page' => self::pagination,
            ]
        ]);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }

        return response()->json($article);
    }
}
