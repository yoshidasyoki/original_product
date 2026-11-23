<?php

namespace App\Http\Controllers\Investment;

use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Http\Requests\Investment\PastSimulations\StoreRequest;
use App\Services\Investment\PastSimulationsService;
use App\Models\InvestmentProduct;
use App\Models\ExchangeRate;

class PastSimulationsController extends Controller
{
    private PastSimulationsService $service;
    public function __construct()
    {
        $this->service = new PastSimulationsService();
    }

    public function simulate(StoreRequest $request, InvestmentProduct $product)
    {
        $form = $request->validated();
        $result = $this->service->simulate($product, $form);

        return response()->json([
            'data' => [
                'conditions' => [
                    'name' => $result['name'],
                    'startMonth' => $result['startMonth'],
                    'endMonth' => $result['endMonth'],
                    'monthlyAmount' => $result['monthlyAmount'],
                    'contributionMonths' => $result['contributionMonths'],
                ],
                'summary' => [
                    'principal' => $result['principal'],
                    'assetValue' => $result['assetValue'],
                    'profit' => $result['profit'],
                    'currency' => $result['currency'],
                    'returnRate' => $result['returnRate'],
                ],
                'chart' => $result['simulationData'],
            ],
        ]);
    }
}
