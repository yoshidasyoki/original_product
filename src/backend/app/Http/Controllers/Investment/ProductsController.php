<?php

namespace App\Http\Controllers\Investment;

use App\Http\Controllers\Controller;
use App\Models\InvestmentProduct;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index()
    {
        $investmentProducts = InvestmentProduct::all(['id', 'name', 'currency']);
        return response()->json([
            'data' => $investmentProducts,
        ]);
    }
}
