<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use App\Http\Requests\User\CreateRequest;

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
}
