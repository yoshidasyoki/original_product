<?php

namespace App\Http\Controllers\Me;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateInfoRequest;
use App\Http\Requests\User\UpdatePasswordRequest;

class AccountController extends Controller
{
    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function update(UpdateInfoRequest $request)
    {
        $form = $request->validated();
        $user = $request->user();

        // 入力パスワードが異なる場合はエラーを返す
        if ($user->password && !Hash::check($form['password'], $user->password)) {
            return response()->json([
                'message' => 'The provided data is invalid.',
                'errors' => [
                    'password' => ['パスワードが一致しません。']
                ]
            ], 422);
        }

        $user->fill([
            'name' => $form['name'],
            'email' => $form['email'],
        ])->save();

        return response()->json([
            'message' => 'User personal info updated successfully.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 200);
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $form = $request->validated();
        $user = $request->user();

        if (!Hash::check($form['current_password'], $user->password)) {
            return response()->json([
                'message' => 'The provided data is invalid.',
                'errors' => [
                    'current_password' => ['現在のパスワードが一致しません。']
                ]
            ], 422);
        }

        $user->fill([
            'password' => Hash::make($form['password']),
        ])->save();

        return response()->json([
            'message' => 'Password updated successfully.',
        ], 200);
    }

    public function destroy(Request $request)
    {
        $user = $request->user();
        $user->delete();

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'User account deleted successfully.',
        ], 200);
    }
}
