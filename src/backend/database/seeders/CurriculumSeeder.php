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
            ['id' => 1, 'title' => '積立投資とNISAの基礎理解', 'position' => 1],
            ['id' => 2, 'title' => '投資可能額の把握', 'position' => 2],
            ['id' => 3, 'title' => '投資シミュレーション', 'position' => 3],
            ['id' => 4, 'title' => '口座開設と商品選択', 'position' => 4],
        ];

        foreach ($curriculums as $curriculum) {
            Curriculum::updateOrCreate(
                ['id' => $curriculum['id']],
                [
                    'title' => $curriculum['title'],
                    'position' => $curriculum['position'],
                ],
            );
        }
    }
}
