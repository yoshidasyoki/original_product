<?php

namespace App\Http\Controllers\Me\LearningProgress;

use App\Http\Controllers\Controller;
use App\Http\Requests\LearningProgress\UpdateOneRequest;
use App\Http\Requests\LearningProgress\UpdateAllRequest;
use App\Models\Curriculum;
use App\Models\UserLearningProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CurriculumsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $result = UserLearningProgress::query()
            ->with('curriculum')
            ->where('user_id', $request->user()->id)
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->curriculum->id,
                    'title' => $row->curriculum->title,
                    'status' => $row->status,
                ];
            });

        return response()->json([
            'data' => $result,
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
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->curriculum->id,
                    'title' => $row->curriculum->title,
                    'status' => $row->status,
                ];
            });

        return response()->json([
            'message' => 'Learning status updated successfully.',
            'data' => $result,
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
            'data' => [
                'id' => $target->curriculum->id,
                'title' => $target->curriculum->title,
                'status' => $target->status,
            ],
        ]);
    }
}
