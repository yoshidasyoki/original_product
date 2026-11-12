<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests\User\CreateRequest;
use App\Http\Requests\User\UpdateInfoRequest;
use App\Http\Requests\User\UpdatePasswordRequest;

use App\Models\User;
use App\Models\Curriculum;
use App\Models\UserLearningProgress;

class UserController extends Controller
{
    public function store(CreateRequest $request)
    {
        $user = DB::transaction(function () use ($request) {
            // ユーザ作成
            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
            ]);

            // ユーザ作成後に全てのカリキュラムに対して学習進捗を作成
            $curriculums = Curriculum::all();
            foreach ($curriculums as $curriculum) {
                UserLearningProgress::create([
                    'user_id' => $user->id,
                    'curriculum_id' => $curriculum->id,
                    'status' => 'not_started',
                ]);
            }

            return $user;
        });

        return response()->json([
            'message' => 'User created successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 201);
    }

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
