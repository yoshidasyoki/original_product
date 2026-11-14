<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\HouseholdCategory;

class HouseholdCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // 支出カテゴリ
            ['name' => '食費', 'type' => 'expense', 'icon' => 'utensils'],
            ['name' => '日用品', 'type' => 'expense', 'icon' => 'shopping-basket'],
            ['name' => '衣服', 'type' => 'expense', 'icon' => 'shirt'],
            ['name' => '美容', 'type' => 'expense', 'icon' => 'scissors'],
            ['name' => '交際費', 'type' => 'expense', 'icon' => 'wine'],
            ['name' => '医療費', 'type' => 'expense', 'icon' => 'heart-pulse'],
            ['name' => '教育費', 'type' => 'expense', 'icon' => 'book-open'],
            ['name' => '光熱費', 'type' => 'expense', 'icon' => 'lightbulb'],
            ['name' => '交通費', 'type' => 'expense', 'icon' => 'train-front'],
            ['name' => '通信費', 'type' => 'expense', 'icon' => 'smartphone'],
            ['name' => '住居費', 'type' => 'expense', 'icon' => 'house'],
            ['name' => '旅行', 'type' => 'expense', 'icon' => 'plane'],
            ['name' => 'その他', 'type' => 'expense', 'icon' => 'piggy-bank'],

            // 収入カテゴリ
            ['name' => '給料', 'type' => 'income', 'icon' => 'briefcase-business'],
            ['name' => 'おこづかい', 'type' => 'income', 'icon' => 'coins'],
            ['name' => '賞与', 'type' => 'income', 'icon' => 'gift'],
            ['name' => 'その他', 'type' => 'income', 'icon' => 'piggy-bank'],
        ];

        foreach ($categories as $category) {
            HouseholdCategory::updateOrCreate(
                [
                    'name' => $category['name'],
                    'type' => $category['type'],
                ],
                [
                    'icon' => $category['icon'],
                ]
            );
        }
    }
}
