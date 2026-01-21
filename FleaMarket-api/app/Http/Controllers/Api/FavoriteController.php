<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\User;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Toggle favorite
     */
    public function toggle(Request $request, string $itemId)
    {
        $item = Item::findOrFail($itemId);
        $user = User::find(auth()->id());

        // 既にお気に入りに追加済みか確認
        if ($user->favorites()->where('item_id', $itemId)->exists()) {
            // 削除
            $user->favorites()->detach($itemId);
            return response()->json([
                'message' => 'お気に入りを解除しました',
                'is_favorited' => false,
            ]);
        } else {
            // 追加
            $user->favorites()->attach($itemId);
            return response()->json([
                'message' => 'お気に入りに追加しました',
                'is_favorited' => true,
            ]);
        }
    }

    /**
     * Get user's favorites
     */
    public function index()
    {
        $user = User::with([
            'favorites' => function ($query) {
                $query->with(['user', 'condition', 'categories'])
                    ->withCount('favorites')
                    ->orderBy('favorites.created_at', 'desc');
            }
        ])->find(auth()->id());

        $favorites = $user->favorites;

        // 各商品にお気に入り状態を追加
        $favorites->transform(function ($item) {
            $item->is_favorited = true; // マイリストなので常にtrue
            return $item;
        });

        // ページネーション形式で返す
        return response()->json([
            'data' => $favorites,
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => 20,
            'total' => $favorites->count(),
        ]);
    }
}
