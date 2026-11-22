<?php

namespace App\Http\Controllers\Me;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use App\Http\Requests\LearningProgress\UpdateOneRequest;
use App\Http\Requests\LearningProgress\UpdateAllRequest;
// use App\Http\Resources\Me\LearningProgress\LearningProgressResource;
use App\Http\Resources\Me\LearningProgressResource;

use App\Models\UserLearningProgress;

class LearningProgressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $result = UserLearningProgress::query()
            ->with('curriculum')
            ->where('user_id', $request->user()->id)
            ->get();

        return response()->json([
            'data' => LearningProgressResource::collection($result),
        ]);
    }

    public function updateAll(UpdateAllRequest $request): JsonResponse
    {
        $form = $request->validated();
        $userId = $request->user()->id;
        $curriculumIds = array_column($form['curriculums'], 'id');

        // 学習状況の更新処理
        DB::transaction(function () use ($form, $userId) {
            foreach ($form['curriculums'] as $curriculum) {
                UserLearningProgress::query()
                    ->where('user_id', $userId)
                    ->where('curriculum_id', $curriculum['id'])
                    ->update([
                        'status' => $curriculum['status'],
                    ]);
            }
        });

        // 更新後の学習状況を取得
        $result = UserLearningProgress::query()
            ->with('curriculum')
            ->where('user_id', $userId)
            ->whereIn('curriculum_id', $curriculumIds)
            ->get();

        return response()->json([
            'message' => 'Learning status updated successfully.',
            'data' => LearningProgressResource::collection($result),
        ]);
    }

    public function updateOne(UpdateOneRequest $request, int $id): JsonResponse
    {
        $form = $request->validated();
        $userId = $request->user()->id;

        // 更新対象を取得
        $target = UserLearningProgress::query()
            ->with('curriculum')
            ->where('user_id', $userId)
            ->where('curriculum_id', $id)
            ->firstOrFail();

        // 学習ステータス更新
        $target->update([
            'status' => $form['status'],
        ]);

        return response()->json([
            'message' => 'Learning status updated successfully.',
            'data' => new LearningProgressResource($target),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()->id;
        $target = UserLearningProgress::query()
            ->where('user_id', $userId)
            ->where('curriculum_id', $id)
            ->firstOrFail();

        return response()->json([
            'data' => new LearningProgressResource($target),
        ]);
    }
}
