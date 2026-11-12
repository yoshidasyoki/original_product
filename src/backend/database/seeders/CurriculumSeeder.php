<?php

namespace Database\Seeders;

use App\Models\Curriculum;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class CurriculumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $curriculums = [
            ['title' => '積立投資とNISAの基礎理解', 'position' => 1],
            ['title' => '投資可能額の把握', 'position' => 2],
            ['title' => '投資シミュレーション', 'position' => 3],
            ['title' => '口座開設と商品選択', 'position' => 4],
        ];

        foreach ($curriculums as $curriculum) {
            Curriculum::create($curriculum);
        }
    }
}
