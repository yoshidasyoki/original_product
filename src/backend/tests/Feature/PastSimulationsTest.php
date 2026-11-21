<?php

namespace Tests\Feature;

use App\Models\InvestmentProduct;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PastSimulationsTest extends TestCase
{
    public function test_simulation_uses_the_requested_currency_to_select_dates_and_value_assets(): void
    {
        foreach ([
            '2026_11_15_203355_create_investment_products_table.php',
            '2026_11_15_203437_create_exchange_rates_table.php',
            '2026_11_15_203507_create_market_prices_table.php',
        ] as $migrationFile) {
            $migration = require database_path('migrations/'.$migrationFile);
            $migration->up();
        }

        $investment = InvestmentProduct::create(['name' => 'sp500tr', 'currency' => 'USD']);
        DB::table('market_prices')->insert([
            ['investment_product_id' => $investment->id, 'record_date' => '2020-01-01', 'price' => 100],
            ['investment_product_id' => $investment->id, 'record_date' => '2020-01-02', 'price' => 125],
            ['investment_product_id' => $investment->id, 'record_date' => '2020-02-01', 'price' => 150],
        ]);
        DB::table('exchange_rates')->insert([
            ['record_date' => '2020-01-01', 'base_currency' => 'USD', 'quote_currency' => 'EUR', 'rate' => 1],
            ['record_date' => '2020-01-02', 'base_currency' => 'USD', 'quote_currency' => 'JPY', 'rate' => 100],
            ['record_date' => '2020-02-01', 'base_currency' => 'USD', 'quote_currency' => 'JPY', 'rate' => 125],
            ['record_date' => '2020-02-01', 'base_currency' => 'USD', 'quote_currency' => 'EUR', 'rate' => 1],
        ]);

        $endpoint = '/api/investment/past-simulations';
        $request = [
            'name' => 'sp500tr',
            'startMonth' => '2020-01',
            'endMonth' => '2020-02',
            'monthlyAmount' => 1500,
        ];

        $this->postJson($endpoint, $request + ['currency' => 'USD'])
            ->assertOk()
            ->assertJsonCount(2, 'data.chart')
            ->assertJsonPath('data.chart.0.month', '2020-01-01')
            ->assertJsonPath('data.summary.currency', 'USD')
            ->assertJsonPath('data.summary.principal', 3000)
            ->assertJsonPath('data.summary.assetValue', 3750);

        $this->postJson($endpoint, $request + ['currency' => 'JPY'])
            ->assertOk()
            ->assertJsonCount(2, 'data.chart')
            ->assertJsonPath('data.chart.0.month', '2020-01-02')
            ->assertJsonPath('data.summary.currency', 'JPY')
            ->assertJsonPath('data.summary.principal', 3000)
            ->assertJsonPath('data.summary.assetValue', 3750);

        $this->postJson($endpoint, $request + ['currency' => 'EUR'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('currency');
    }
}
