<?php

namespace Tests\Feature;

use App\Models\HouseholdCategory;
use App\Models\User;
use Tests\TestCase;

class CategoriesOptionalTypeTest extends TestCase
{
    public function test_categories_can_be_listed_with_or_without_a_type(): void
    {
        $migration = require database_path('migrations/2026_11_14_045933_create_household_categories_table.php');
        $migration->up();

        $expense = HouseholdCategory::create(['name' => '食費', 'type' => 'expense', 'icon' => 'utensils']);
        $income = HouseholdCategory::create(['name' => '給与', 'type' => 'income', 'icon' => 'wallet']);
        $this->actingAs(User::factory()->make());

        $this->getJson('/api/household/categories')
            ->assertOk()
            ->assertExactJson(['data' => [
                'type' => null,
                'categories' => [
                    $expense->only(['id', 'name', 'icon']),
                    $income->only(['id', 'name', 'icon']),
                ],
            ]]);

        foreach ([$expense, $income] as $category) {
            $this->getJson('/api/household/categories?type='.$category->type)
                ->assertOk()
                ->assertExactJson(['data' => [
                    'type' => $category->type,
                    'categories' => [$category->only(['id', 'name', 'icon'])],
                ]]);
        }

        foreach (['?type=', '?type=invalid', '?type[]=income'] as $query) {
            $this->getJson('/api/household/categories'.$query)
                ->assertUnprocessable()
                ->assertJsonValidationErrors('type');
        }
    }
}
