<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

use App\Models\ExchangeRate;

class ExchangeRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $base = 'USD';
        $quote = 'JPY';
        $startYear = 2000;
        $endYear = now()->year;

        for ($year = $startYear; $year <= $endYear; $year += 5) {
            $from = "{$year}-01-01";
            $toYear = min($year + 4, $endYear);
            $to = "{$toYear}-12-31";

            $response = Http::get('https://api.frankfurter.dev/v2/rates', [
                'from' => $from,
                'to' => $to,
                'base' => $base,
                'quotes' => $quote,
            ])->throw();

            $results = $response->json();
            $rows = array_map(function ($row) {
                return [
                    'base_currency' => $row['base'],
                    'quote_currency' => $row['quote'],
                    'rate' => $row['rate'],
                    'record_date' => $row['date'],
                ];
            }, $results);

            ExchangeRate::upsert(
                $rows,
                ['base_currency', 'quote_currency', 'record_date'],
                ['rate']
            );

            echo "Seeded exchange rates from {$from} to {$to}.\n";
        }
    }
}
