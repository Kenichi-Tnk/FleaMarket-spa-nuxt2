<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    /**
     * Get comments for an item
     */
    public function index(string $itemId)
    {
        $item = Item::findOrFail($itemId);

        $comments = $item->comments()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['comments' => $comments]);
    }

    /**
     * Store a new comment
     */
    public function store(Request $request, string $itemId)
    {
        $item = Item::findOrFail($itemId);

        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'item_id' => $itemId,
            'content' => $request->content,
        ]);

        $comment->load('user');

        return response()->json([
            'message' => 'コメントを投稿しました',
            'comment' => $comment,
        ], 201);
    }

    /**
     * Delete a comment
     */
    public function destroy(string $itemId, string $commentId)
    {
        $comment = Comment::where('item_id', $itemId)
            ->where('id', $commentId)
            ->firstOrFail();

        // コメント投稿者本人のみ削除可能
        if ($comment->user_id !== auth()->id()) {
            return response()->json(['error' => '権限がありません'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'コメントを削除しました']);
    }
}
