<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

use App\Http\Requests\AuthRequest;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    public function login(AuthRequest $request): JsonResponse
    {
        if (!Auth::guard('web')->attempt($request->validated())) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $request->session()->regenerate();

        $user = Auth::guard('web')->user();
        return response()->json([
            'message' => 'Login successful.',
            'data' => new UserResource($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
