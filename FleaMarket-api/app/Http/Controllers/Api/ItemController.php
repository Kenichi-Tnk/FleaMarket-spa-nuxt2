<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Item::with(['user', 'condition', 'categories'])
            ->withCount('favorites')
            ->orderBy('created_at', 'desc');

        // 自分の出品商品のみ取得する場合
        if ($request->has('my_items') && $request->my_items && auth()->check()) {
            $query->where('user_id', auth()->id());
        }
        // 通常のおすすめ表示：ログインユーザーが出品した商品を除外
        elseif (auth()->check()) {
            $query->where('user_id', '!=', auth()->id());
        }

        // 検索条件
        if ($request->has('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        // カテゴリーフィルター
        if ($request->has('category_id')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        $items = $query->paginate(20);

        // 各商品にログインユーザーのお気に入り状態を追加
        if (auth()->check()) {
            $items->getCollection()->transform(function ($item) {
                $item->is_favorited = $item->favorites()
                    ->where('user_id', auth()->id())
                    ->exists();
                return $item;
            });
        }

        return response()->json($items);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'description' => 'required|string',
            'price' => 'required|integer|min:1',
            'image' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:10240',
            'condition_id' => 'required|exists:conditions,id',
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 画像をアップロード
        $imgUrl = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads/items', $filename, 'public');
            $imgUrl = $path;
        }

        $item = Item::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'brand' => $request->brand,
            'description' => $request->description,
            'price' => $request->price,
            'img_url' => $imgUrl,
            'condition_id' => $request->condition_id,
        ]);

        // カテゴリー紐付け
        $item->categories()->attach($request->category_ids);

        return response()->json([
            'message' => '商品を出品しました',
            'item' => $item->load(['condition', 'categories']),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = Item::with([
            'user.profile',
            'condition',
            'categories',
            'comments.user',
            'favorites'
        ])->withCount('favorites')->findOrFail($id);

        // ログインユーザーがお気に入りに追加しているか確認
        $item->is_favorited = false;
        if (auth()->check()) {
            $item->is_favorited = $item->favorites()
                ->where('user_id', auth()->id())
                ->exists();
        }

        return response()->json(['item' => $item]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $item = Item::findOrFail($id);

        // 出品者本人のみ更新可能
        if ($item->user_id !== auth()->id()) {
            return response()->json(['error' => '権限がありません'], 403);
        }

        // 売り切れの商品は更新不可
        if ($item->is_sold) {
            return response()->json(['error' => '売り切れの商品は更新できません'], 400);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|integer|min:1',
            'condition_id' => 'sometimes|required|exists:conditions,id',
            'category_ids' => 'sometimes|required|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item->update($request->only([
            'name',
            'brand',
            'description',
            'price',
            'condition_id'
        ]));

        if ($request->has('category_ids')) {
            $item->categories()->sync($request->category_ids);
        }

        return response()->json([
            'message' => '商品を更新しました',
            'item' => $item->load(['condition', 'categories']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = Item::findOrFail($id);

        // 出品者本人のみ削除可能
        if ($item->user_id !== auth()->id()) {
            return response()->json(['error' => '権限がありません'], 403);
        }

        // 売り切れの商品は削除不可
        if ($item->is_sold) {
            return response()->json(['error' => '売り切れの商品は削除できません'], 400);
        }

        $item->delete();

        return response()->json(['message' => '商品を削除しました']);
    }
}
