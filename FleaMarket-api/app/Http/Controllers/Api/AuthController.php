<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // プロフィール作成
        $user->profile()->create([]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'user' => $user,
            'access_token' => $token,
        ], 201);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'メールアドレスまたはパスワードが正しくありません'], 401);
        }

        $user = auth()->user();

        return response()->json([
            'user' => $user,
            'access_token' => $token,
        ]);
    }

    /**
     * Logout user
     */
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'ログアウトしました']);
    }

    /**
     * Get authenticated user
     */
    public function me()
    {
        $user = User::with('profile')->find(auth()->id());

        return response()->json(['user' => $user]);
    }
}
