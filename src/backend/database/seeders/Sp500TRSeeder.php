<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\InvestmentProduct;
use App\Models\MarketPrice;

class Sp500TRSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // CSVファイルの読込み
        $path = storage_path('app/imports/sp500tr.csv');
        $file = fopen($path, 'r');

        // 投資商品・指数を登録
        $product = InvestmentProduct::query()
            ->where('name', 'sp500tr')
            ->first();
        $productId = $product?->id;

        if ($productId === null) {
            $productId = InvestmentProduct::insertGetId([
                'name' => 'sp500tr',
                'currency' => 'USD',
            ]);
        }

        // 価格データを登録
        $rows = [];

        while (($data = fgetcsv($file)) !== false) {
            if (count($data) !== 2 || !is_numeric($data[1])) {
                continue;
            }

            $rows[] = [
                'investment_product_id' => $productId,
                'record_date' => $data[0],
                'price' => $data[1],
            ];

            // 500件ずつ登録
            if (count($rows) >= 500) {
                MarketPrice::upsert(
                    $rows,
                    ['investment_product_id', 'record_date'],
                    ['price']
                );

                $rows = [];
            }
        }

        if (!empty($rows)) {
            MarketPrice::upsert(
                $rows,
                ['investment_product_id', 'record_date'],
                ['price']
            );
        }

        // CSVファイルを閉じる
        fclose($file);
    }
}
