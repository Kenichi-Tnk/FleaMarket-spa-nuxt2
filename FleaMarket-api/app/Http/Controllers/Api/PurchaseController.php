<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseController extends Controller
{
    /**
     * Purchase an item
     */
    public function store(Request $request, string $itemId)
    {
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|in:convenience,card',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $item = Item::findOrFail($itemId);

        // 販売済みチェック
        if ($item->is_sold) {
            return response()->json(['error' => 'この商品は売り切れです'], 400);
        }

        // 自分の商品は購入できない
        if ($item->user_id === auth()->id()) {
            return response()->json(['error' => '自分の商品は購入できません'], 400);
        }

        // トランザクション処理
        DB::beginTransaction();
        try {
            // 購入記録作成
            $purchase = Purchase::create([
                'user_id' => auth()->id(),
                'item_id' => $itemId,
                'payment_method' => $request->payment_method,
            ]);

            // 商品を売り切れにする
            $item->update(['is_sold' => true]);

            DB::commit();

            return response()->json([
                'message' => '購入が完了しました',
                'purchase' => $purchase->load('item'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => '購入処理に失敗しました'], 500);
        }
    }

    /**
     * Get user's purchase history
     */
    public function index()
    {
        $purchases = Purchase::with(['item.condition', 'item.categories'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($purchases);
    }

    /**
     * Get purchase detail
     */
    public function show(string $id)
    {
        $purchase = Purchase::with(['item.condition', 'item.categories'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return response()->json(['purchase' => $purchase]);
    }
}
