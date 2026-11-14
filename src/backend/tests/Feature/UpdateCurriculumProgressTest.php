<?php

namespace Tests\Feature;

use App\Models\Curriculum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UpdateCurriculumProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_one_curriculum_and_receive_its_title(): void
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();
        $curriculums = $this->createCurriculums();
        $this->createProgress($user, $curriculums[0]);
        $this->createProgress($user, $curriculums[1]);
        $this->createProgress($otherUser, $curriculums[0]);
        $url = '/api/me/learning-progress/curriculums/'.$curriculums[0]->id;

        // 同じステータスを再送しても成功する。
        foreach ([1, 2] as $attempt) {
            $this->actingAs($user)->putJson($url, ['status' => 'completed'])
                ->assertOk()->assertExactJson([
                    'message' => 'Learning status updated successfully.',
                    'data' => [
                        'id' => $curriculums[0]->id,
                        'title' => $curriculums[0]->title,
                        'status' => 'completed',
                    ],
                ]);
        }

        $this->assertDatabaseHas('user_learning_progress', [
            'user_id' => $user->id,
            'curriculum_id' => $curriculums[0]->id,
            'status' => 'completed',
        ]);
        $this->assertDatabaseHas('user_learning_progress', [
            'user_id' => $user->id,
            'curriculum_id' => $curriculums[1]->id,
            'status' => 'not_started',
        ]);
        $this->assertDatabaseHas('user_learning_progress', [
            'user_id' => $otherUser->id,
            'curriculum_id' => $curriculums[0]->id,
            'status' => 'not_started',
        ]);
    }

    public function test_single_update_rejects_guests_and_invalid_statuses(): void
    {
        $user = $this->createUser();
        $curriculum = $this->createCurriculums()[0];
        $this->createProgress($user, $curriculum);
        $url = '/api/me/learning-progress/curriculums/'.$curriculum->id;

        $this->putJson($url, ['status' => 'completed'])->assertUnauthorized();

        foreach ([[], ['status' => 'invalid']] as $payload) {
            $this->actingAs($user)->putJson($url, $payload)
                ->assertUnprocessable()->assertJsonValidationErrors('status');
        }

        $this->assertDatabaseMissing('user_learning_progress', ['status' => 'completed']);
    }

    public function test_single_update_returns_not_found_for_progress_the_user_does_not_have(): void
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();
        $curriculum = $this->createCurriculums()[0];
        $this->createProgress($otherUser, $curriculum);

        foreach ([$curriculum->id, 999999] as $id) {
            $this->actingAs($user)
                ->putJson('/api/me/learning-progress/curriculums/'.$id, ['status' => 'completed'])
                ->assertNotFound();
        }

        $this->assertDatabaseMissing('user_learning_progress', ['status' => 'completed']);
    }

    public function test_guest_cannot_update_progress(): void
    {
        $this->putJson('/api/me/learning-progress/curriculums', [])->assertUnauthorized();
    }

    public function test_user_can_update_multiple_statuses_without_changing_other_progress(): void
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();
        $curriculums = $this->createCurriculums();

        foreach ([$user, $otherUser] as $owner) {
            foreach ($curriculums as $curriculum) {
                $this->createProgress($owner, $curriculum);
            }
        }

        $payload = ['curriculums' => [
            ['id' => $curriculums[0]->id, 'status' => 'completed'],
            ['id' => $curriculums[1]->id, 'status' => 'not_started'],
        ]];

        $this->actingAs($user)->putJson('/api/me/learning-progress/curriculums', $payload)
            ->assertOk()->assertExactJson([
                'message' => 'Learning status updated successfully.',
                'data' => [
                    [
                        'id' => $curriculums[0]->id,
                        'title' => $curriculums[0]->title,
                        'status' => 'completed',
                    ],
                    [
                        'id' => $curriculums[1]->id,
                        'title' => $curriculums[1]->title,
                        'status' => 'not_started',
                    ],
                ],
            ]);

        foreach ($payload['curriculums'] as $curriculum) {
            $this->assertDatabaseHas('user_learning_progress', [
                'user_id' => $user->id,
                'curriculum_id' => $curriculum['id'],
                'status' => $curriculum['status'],
            ]);
        }

        $this->assertDatabaseHas('user_learning_progress', [
            'user_id' => $user->id,
            'curriculum_id' => $curriculums[2]->id,
            'status' => 'not_started',
        ]);
        $this->assertDatabaseMissing('user_learning_progress', [
            'user_id' => $otherUser->id,
            'status' => 'completed',
        ]);
    }

    public function test_invalid_batch_is_rejected_before_any_update(): void
    {
        $user = $this->createUser();
        $otherUser = $this->createUser();
        $curriculums = $this->createCurriculums();
        $this->createProgress($user, $curriculums[0]);
        $this->createProgress($otherUser, $curriculums[1]);

        $invalidItems = [
            ['id' => $curriculums[1]->id, 'status' => 'completed'],
            ['id' => 999999, 'status' => 'completed'],
            ['id' => $curriculums[0]->id, 'status' => 'completed'],
            ['id' => $curriculums[0]->id, 'status' => 'invalid'],
            ['status' => 'completed'],
            ['id' => $curriculums[0]->id],
        ];

        foreach ($invalidItems as $item) {
            $this->actingAs($user)->putJson('/api/me/learning-progress/curriculums', [
                'curriculums' => [
                    ['id' => $curriculums[0]->id, 'status' => 'completed'],
                    $item,
                ],
            ])->assertUnprocessable();
        }

        $this->actingAs($user)->putJson('/api/me/learning-progress/curriculums', [
            'curriculums' => [],
        ])->assertUnprocessable();

        $this->assertDatabaseMissing('user_learning_progress', ['status' => 'completed']);
    }

    private function createUser(): User
    {
        $user = User::factory()->make();
        unset($user->email_verified_at, $user->remember_token);
        $user->save();

        return $user;
    }

    private function createCurriculums(): array
    {
        $curriculums = [];

        foreach ([1, 2, 3] as $position) {
            $curriculums[] = Curriculum::create([
                'title' => 'Curriculum '.$position,
                'position' => $position,
            ]);
        }

        return $curriculums;
    }

    private function createProgress(User $user, Curriculum $curriculum): void
    {
        DB::table('user_learning_progress')->insert([
            'user_id' => $user->id,
            'curriculum_id' => $curriculum->id,
            'status' => 'not_started',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
