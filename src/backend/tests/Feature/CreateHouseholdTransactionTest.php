<?php

namespace Tests\Feature;

use App\Models\HouseholdCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateHouseholdTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_transaction(): void
    {
        $user = $this->createUser();
        $category = $this->createCategory();
        $payload = $this->payload($category);

        $response = $this->actingAs($user)->postJson('/api/household/transactions', $payload);

        $response->assertCreated()->assertExactJson([
            'message' => 'Household entry created successfully.',
            'data' => [
                'id' => $response->json('data.id'),
                'date' => $payload['date'],
                'type' => 'expense',
                'category' => $category->only(['id', 'name', 'icon']),
                'memo' => $payload['memo'],
                'amount' => 3500,
            ],
        ]);
        $this->assertDatabaseHas('household_transactions', [
            'id' => $response->json('data.id'),
            'user_id' => $user->id,
            'household_category_id' => $category->id,
            'record_date' => $payload['date'],
            'memo' => $payload['memo'],
            'amount' => 3500,
        ]);
    }

    public function test_memo_is_optional_and_income_is_supported(): void
    {
        $category = $this->createCategory();
        $category->update(['type' => 'income']);
        $payload = $this->payload($category);
        $payload['type'] = 'income';
        unset($payload['memo']);

        $this->actingAs($this->createUser())->postJson('/api/household/transactions', $payload)
            ->assertCreated()
            ->assertJsonPath('data.type', 'income')
            ->assertJsonPath('data.memo', null);
    }

    public function test_guests_cannot_create_transactions(): void
    {
        $this->postJson('/api/household/transactions', [])->assertUnauthorized();
        $this->assertDatabaseCount('household_transactions', 0);
    }

    public function test_invalid_input_does_not_create_transactions(): void
    {
        $category = $this->createCategory();
        $this->actingAs($this->createUser());

        foreach ([
            ['date' => '2026-02-30'],
            ['type' => 'invalid'],
            ['type' => ['expense']],
            ['type' => 'income'],
            ['categoryId' => 999999],
            ['memo' => str_repeat('あ', 51)],
            ['amount' => 0],
            ['amount' => -1],
            ['amount' => 1.5],
            ['amount' => 2147483648],
        ] as $invalid) {
            $this->postJson('/api/household/transactions', array_replace($this->payload($category), $invalid))
                ->assertUnprocessable()
                ->assertJsonValidationErrors(array_key_first($invalid) === 'type' && $invalid['type'] === 'income' ? 'categoryId' : array_key_first($invalid));
        }

        $this->postJson('/api/household/transactions', [])->assertUnprocessable()
            ->assertJsonValidationErrors(['date', 'type', 'categoryId', 'amount']);
        $this->assertDatabaseCount('household_transactions', 0);
    }

    private function createUser(): User
    {
        $user = User::factory()->make();
        unset($user->email_verified_at, $user->remember_token);
        $user->save();

        return $user;
    }

    private function createCategory(): HouseholdCategory
    {
        return HouseholdCategory::create(['name' => '食費', 'type' => 'expense', 'icon' => 'utensils']);
    }

    private function payload(HouseholdCategory $category): array
    {
        return [
            'date' => '2026-10-25',
            'type' => 'expense',
            'categoryId' => $category->id,
            'memo' => 'スーパーで買い物',
            'amount' => 3500,
        ];
    }
}
