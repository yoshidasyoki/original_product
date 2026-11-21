<?php

namespace App\Http\Controllers\Investment;

use App\Http\Controllers\Controller;
use App\Models\InvestmentProduct;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index()
    {
        $products = InvestmentProduct::query()
            ->join(
                'market_prices',
                'investment_products.id',
                '=',
                'market_prices.investment_product_id'
            )
            ->select(
                'investment_products.id',
                'name',
                'currency'
            )
            ->selectRaw('MIN(market_prices.record_date) AS startDate')
            ->selectRaw('MAX(market_prices.record_date) AS endDate')
            ->groupBy('investment_products.id')
            ->get();

        return response()->json([
            'data' => $products,
        ]);
    }
}
