<?php

namespace App\Http\Controllers\Household;

use Carbon\Carbon;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Requests\Household\Transactions\IndexRequest;
use App\Http\Requests\Household\Transactions\SaveRequest;

use App\Models\HouseholdTransaction;

class TransactionsController extends Controller
{
    public function index(IndexRequest $request)
    {
        $month = $request->query('month') ?? now()->format('Y-m');

        // 指定した年月の月初と月末の日付を取得
        $date = Carbon::createFromFormat('!Y-m', $month);
        $startDate = $date->copy()->startOfMonth()->toDateString();
        $endDate = $date->copy()->endOfMonth()->toDateString();

        $transactions = HouseholdTransaction::query()
            ->with('householdCategory')
            ->where('user_id', $request->user()->id)
            ->whereBetween('record_date', [$startDate, $endDate])
            ->orderByDesc('record_date')
            ->get();

        $result = [];
        foreach ($transactions as $transaction) {
            $category = $transaction->householdCategory;
            $result[] = [
                'id' => $transaction->id,
                'date' => $transaction->record_date,
                'type' => $category->type,
                'category' => $category->only(['id', 'name', 'icon']),
                'memo' => $transaction->memo,
                'amount' => $transaction->amount,
            ];
        }

        return response()->json([
            'data' => $result,
        ]);
    }

    public function store(SaveRequest $request): JsonResponse
    {
        $form = $request->validated();

        $transaction = HouseholdTransaction::create([
            'user_id' => $request->user()->id,
            'household_category_id' => $form['categoryId'],
            'record_date' => $form['date'],
            'memo' => $form['memo'],
            'amount' => $form['amount'],
        ]);
        $category = $transaction->householdCategory;

        return response()->json([
            'message' => 'Household entry created successfully.',
            'data' => [
                'id' => $transaction->id,
                'date' => $transaction->record_date,
                'type' => $category->type,
                'category' => $category->only(['id', 'name', 'icon']),
                'memo' => $transaction->memo,
                'amount' => $transaction->amount,
            ],
        ], 201);
    }

    public function show(Request $request, HouseholdTransaction $transaction): JsonResponse
    {
        $transaction->load('householdCategory');
        $category = $transaction->householdCategory;

        return response()->json([
            'data' => [
                'id' => $transaction->id,
                'date' => $transaction->record_date,
                'type' => $category->type,
                'category' => $category->only(['id', 'name', 'icon']),
                'memo' => $transaction->memo,
                'amount' => $transaction->amount,
            ],
        ]);
    }

    public function update(SaveRequest $request, HouseholdTransaction $transaction): JsonResponse
    {
        $form = $request->validated();

        $transaction->update([
            'household_category_id' => $form['categoryId'],
            'record_date' => $form['date'],
            'memo' => $form['memo'],
            'amount' => $form['amount'],
        ]);
        $category = $transaction->householdCategory;

        return response()->json([
            'message' => 'Household entry updated successfully.',
            'data' => [
                'id' => $transaction->id,
                'date' => $transaction->record_date,
                'type' => $category->type,
                'category' => $category->only(['id', 'name', 'icon']),
                'memo' => $transaction->memo,
                'amount' => $transaction->amount,
            ],
        ]);
    }

    public function destroy(Request $request, HouseholdTransaction $transaction): JsonResponse
    {
        $transaction->delete();

        return response()->json([
            'message' => 'Household entry deleted successfully.',
        ]);
    }
}
