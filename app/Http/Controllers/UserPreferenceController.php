<?php

namespace App\Http\Controllers;

use App\Http\Requests\SetUserPreferenceRequest;
use App\Models\Article;
use App\Models\UserPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserPreferenceController extends Controller
{
    protected const PAGINATE_LIMIT = 10;
    /**
     * @param SetUserPreferenceRequest $request
     * @return JsonResponse
     */
    public function setUserPreference(SetUserPreferenceRequest $request): JsonResponse
    {
        // Find or create user preferences
        UserPreference::updateOrCreate(
            [
                'user_id' => Auth::id()
            ],
            [
                'sources' => $request->input('sources'),
                'categories' => $request->input('categories'),
                'authors' => $request->input('authors')
            ]
        );

        return response()->json(['message' => 'Preferences updated successfully']);
    }

    /**
     * @return JsonResponse
     */
    public function getUserPreferences(): JsonResponse
    {
        $userId = Auth::id();
        $preferences = UserPreference::where('user_id', $userId)->first();
        if (!$preferences) {
            return response()->json(['message' => 'No preferences found'], 404);
        }

        return response()->json([
            'sources' => $preferences->sources,
            'categories' => $preferences->categories,
            'authors' => $preferences->authors
        ]);
    }

    /**
     * It will return news feed based on user preference
     *
     * @return JsonResponse
     */
    public function getNewsFeed()
    {
        $userId = Auth::id();
        $preferences = UserPreference::where('user_id', $userId)->first();
        $newsArticles = Article::when(collect($preferences)->isNotEmpty(), function ($query) use ($preferences) {
            $query->whereIn('source', $preferences->sources)->orWhereIn('category', $preferences->categories)
            ->orWhereIn('author', $preferences->authors);
        })->paginate(self::PAGINATE_LIMIT);

        return response()->json([
            'data' => $newsArticles->items(),
            'pagination' => [
                'current_page' => $newsArticles->currentPage(),
                'total_pages' => ceil($newsArticles->total() / self::PAGINATE_LIMIT),
                'per_page' => self::PAGINATE_LIMIT,
            ]
        ]);
    }
}
