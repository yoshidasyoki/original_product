<?php

namespace App\Http\Controllers\Investment;

use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Http\Requests\Investment\PastSimulations\StoreRequest;

use App\Models\InvestmentProduct;
use App\Models\ExchangeRate;

class PastSimulationsController extends Controller
{
    public function store(StoreRequest $request, InvestmentProduct $product)
    {
        $form = $request->validated();

        $searchCondition = $this->getSearchCondition($product, $form);
        $pastPrices = $this->getPastPrices($searchCondition);

        if ($searchCondition['requestCurrency'] !== $searchCondition['defaultCurrency']) {
            $dates = array_column($pastPrices['body'], 'record_date');
            $exchangeRates = $this->getExchangeRates(
                $dates,
                $searchCondition['defaultCurrency'],
                $searchCondition['requestCurrency']
            );
            $pastPrices = $this->convertRate($pastPrices, $exchangeRates);
        }

        $result = $this->simulate($pastPrices, (int) $form['monthlyAmount']);

        $totalPrincipal = end($result)['principal'];
        $totalAssetValue = end($result)['assetValue'];

        $summary = [
            'principal' => $totalPrincipal,
            'assetValue' => $totalAssetValue,
            'profit' => $totalAssetValue - $totalPrincipal,
            'currency' => $form['currency'],
            'returnRate' => $totalAssetValue / $totalPrincipal,
        ];

        $conditions = [
            'name' => $searchCondition['name'],
            'startMonth' => reset($result)['month'],
            'endMonth' => end($result)['month'],
            'monthlyAmount' => $form['monthlyAmount'],
            'contributionMonths' => count($result),
        ];

        return response()->json([
            'data' => [
                'conditions' => $conditions,
                'summary' => $summary,
                'chart' => $result,
            ]
        ]);
    }

    // シミュレーションの検索条件を整理するメソッド
    private function getSearchCondition(InvestmentProduct $product, array $form)
    {
        // market_pricesテーブルに登録されている月初データを配列で取得
        $startDate = Carbon::createFromFormat('!Y-m', $form['startMonth'])->startOfMonth();
        $endDate = Carbon::createFromFormat('!Y-m', $form['endMonth'])->endOfMonth();
        $monthlyFirstDates = InvestmentProduct::query()
            ->join(
                'market_prices',
                'investment_products.id',
                '=',
                'market_prices.investment_product_id'
            )
            ->join(
                'exchange_rates',
                'market_prices.record_date',
                '=',
                'exchange_rates.record_date'
            )
            ->where('investment_products.name', $product->name)
            ->where('exchange_rates.base_currency', $product->currency)
            ->whereBetween('market_prices.record_date', [$startDate, $endDate])
            ->selectRaw('MIN(market_prices.record_date)')
            ->groupByRaw(
                'YEAR(market_prices.record_date),
                MONTH(market_prices.record_date)'
            );

        // ファンドの名前と通貨情報、月初データを返す
        return [
            'name' => $product->name,
            'searchDates' => $monthlyFirstDates,
            'defaultCurrency' => $product->currency,
            'requestCurrency' => $form['currency'],
        ];
    }

    // 指定した条件の株価データを取得するメソッド
    private function getPastPrices(array $searchCondition)
    {
        $simulationData = InvestmentProduct::query()
            ->join(
                'market_prices',
                'investment_products.id',
                '=',
                'market_prices.investment_product_id'
            )
            ->where('investment_products.name', $searchCondition['name'])
            ->whereIn('market_prices.record_date', $searchCondition['searchDates'])
            ->orderBy('market_prices.record_date')
            ->get([
                'price',
                'record_date',
            ])
            ->toArray();

        return [
            'metaData' => [
                'name' => $searchCondition['name'],
                'currency' => $searchCondition['defaultCurrency'],
            ],
            'body' => $simulationData,
        ];
    }

    // 指定した日付データの為替レートを取得するメソッド
    private function getExchangeRates(array $dates, string $baseCurrency, string $quoteCurrency)
    {
        $exchangeRates = ExchangeRate::query()
            ->whereIn('record_date', $dates)
            ->where('base_currency', $baseCurrency)
            ->where('quote_currency', $quoteCurrency)
            ->get(['record_date', 'rate',])
            ->keyBy('record_date')
            ->toArray();

        return [
            'metaData' => [
                'baseCurrency' => $baseCurrency,
                'quoteCurrency' => $quoteCurrency,
            ],
            'body' => [...$exchangeRates],
        ];
    }

    // ファンド・指数の通貨単位をリクエストの通貨に換算する
    private function convertRate(array $pastPrices, array $exchangeRates): array
    {
        $rateRecords = $exchangeRates['body'];  // 為替レートの配列データ
        $pastPriceRecords = $pastPrices['body'];

        $convertedPrices = [
            'name' => $pastPrices['metaData']['name'],
            'currency' => $exchangeRates['metaData']['quoteCurrency'],
            'body' => [],
        ];
        foreach ($pastPriceRecords as $pastPriceRecord) {
            $rate = $rateRecords[$pastPriceRecord['record_date']]['rate'];
            $convertedPrice = $pastPriceRecord['price'] * $rate;
            $convertedPrices['body'][] = [
                'price' => $convertedPrice,
                'record_date' => $pastPriceRecord['record_date'],
            ];
        }

        return $convertedPrices;
    }

    // シミュレーション処理
    private function simulate(array $pastPrices, int $monthlyAmount)
    {
        $result = [];

        $pastPriceRecords = $pastPrices['body'];
        $principal = 0;     // 総積立額を格納する変数
        $totalUnits = 0;    // 保有する総口数を格納する変数
        foreach ($pastPriceRecords as $pastPriceRecord) {
            $purchasedUnits = $monthlyAmount / $pastPriceRecord['price'];   // 購入口数を計算

            $principal += $monthlyAmount;
            $totalUnits += $purchasedUnits;

            $asset = $totalUnits * $pastPriceRecord['price'];   // 毎月の総資産額を計算

            $result[] = [
                'month' => $pastPriceRecord['record_date'],
                'principal' => $principal,
                'assetValue' => (int) $asset,
            ];
        }

        return $result;
    }
}
