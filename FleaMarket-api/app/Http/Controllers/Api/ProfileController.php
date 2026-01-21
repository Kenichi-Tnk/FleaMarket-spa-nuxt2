<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        $user = User::with('profile')->find(auth()->id());

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:8',
            'address' => 'required|string|max:255',
            'building' => 'nullable|string|max:255',
            'profile_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:10240', // 10MB
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // ユーザー名を更新
        $user->name = $request->name;
        $user->save();

        // プロフィール画像のアップロード処理
        $imgUrl = $user->profile->img_url ?? null;
        if ($request->hasFile('profile_image')) {
            // 古い画像を削除
            if ($imgUrl && Storage::disk('public')->exists($imgUrl)) {
                Storage::disk('public')->delete($imgUrl);
            }

            // 新しい画像を保存
            $file = $request->file('profile_image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads/profiles', $filename, 'public');
            $imgUrl = $path;
        }

        // プロフィール情報を更新
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'postal_code' => $request->postal_code,
                'address' => $request->address,
                'building' => $request->building,
                'img_url' => $imgUrl,
            ]
        );

        // 更新後のユーザー情報を取得
        $user->load('profile');

        return response()->json([
            'message' => 'プロフィールを更新しました',
            'user' => $user,
        ]);
    }
}
