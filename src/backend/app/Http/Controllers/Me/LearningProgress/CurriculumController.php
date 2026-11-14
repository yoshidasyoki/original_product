<?php

namespace App\Http\Controllers\Me\LearningProgress;

use App\Http\Controllers\Controller;
use App\Http\Requests\LearningProgress\UpdateCurriculumRequest;
use App\Http\Requests\LearningProgress\UpdateCurriculumsRequest;
use App\Models\Curriculum;
use App\Models\UserLearningProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CurriculumController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $curriculums =  Curriculum::query()
            ->join(
                'user_learning_progress',
                'curriculums.id',
                '=',
                'user_learning_progress.curriculum_id'
            )
            ->where('user_learning_progress.user_id', $request->user()->id)
            ->orderBy('curriculums.position')
            ->get([
                'curriculums.id',
                'curriculums.title',
                'user_learning_progress.status',
            ]);

        return response()->json([
            'data' => $curriculums,
        ]);
    }

    public function updateAll(UpdateCurriculumsRequest $request): JsonResponse
    {
        $form = $request->validated();
        $userId = $request->user()->id;
        $curriculumIds = array_column($form['curriculums'], 'id');

        DB::transaction(function () use ($form, $userId) {
            // 各カリキュラムの学習状況を一括更新
            foreach ($form['curriculums'] as $curriculum) {
                UserLearningProgress::query()
                    ->where('user_id', $userId)
                    ->where('curriculum_id', $curriculum['id'])
                    ->update([
                        'status' => $curriculum['status'],
                    ]);
            }
        });

        // 更新後の学習状況をレスポンス用に取得
        $result = Curriculum::query()
            ->join(
                'user_learning_progress',
                'curriculums.id',
                '=',
                'user_learning_progress.curriculum_id'
            )
            ->where('user_learning_progress.user_id', $userId)
            ->whereIn('curriculums.id', $curriculumIds)
            ->orderBy('curriculums.position')
            ->get([
                'curriculums.id',
                'curriculums.title',
                'user_learning_progress.status',
            ])
            ->toArray();

        return response()->json([
            'message' => 'Learning status updated successfully.',
            'data' => $result,
        ]);
    }

    public function updateOne(UpdateCurriculumRequest $request, int $id): JsonResponse
    {
        $form = $request->validated();
        $userId = $request->user()->id;

        UserLearningProgress::query()
            ->where('user_id', $userId)
            ->where('curriculum_id', $id)
            ->update([
                'status' => $form['status'],
            ]);

        $result = Curriculum::query()
            ->join(
                'user_learning_progress',
                'curriculums.id',
                '=',
                'user_learning_progress.curriculum_id'
            )
            ->where('user_learning_progress.user_id', $userId)
            ->where('curriculums.id', $id)
            ->firstOrFail([
                'curriculums.id',
                'curriculums.title',
                'user_learning_progress.status',
            ]);

        return response()->json([
            'message' => 'Learning status updated successfully.',
            'data' => $result,
        ]);
    }
}
