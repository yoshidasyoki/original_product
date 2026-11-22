<?php

namespace App\Http\Controllers\Household;

use Carbon\Carbon;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

use App\Http\Controllers\Controller;
use App\Http\Requests\Household\Transactions\IndexRequest;
use App\Http\Requests\Household\Transactions\SaveRequest;
use App\Http\Resources\Household\TransactionResource;

use App\Models\HouseholdTransaction;

class TransactionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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

        $income = $transactions
            ->where('householdCategory.type', 'income')
            ->sum('amount');

        $expense = $transactions
            ->where('householdCategory.type', 'expense')
            ->sum('amount');

        $balance = $income - $expense;

        return response()->json([
            'data' => [
                'period' => [
                    'year' => $date->year,
                    'month' => $date->month,
                ],
                'summary' => [
                    'income' => $income,
                    'expense' => $expense,
                    'balance' => $balance,
                ],
                'transactions' => TransactionResource::collection($transactions)
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
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

        return response()->json([
            'message' => 'Household entry created successfully.',
            'data' => new TransactionResource($transaction),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(HouseholdTransaction $transaction): JsonResponse
    {
        Gate::authorize('view', $transaction);
        return response()->json([
            'data' => new TransactionResource($transaction),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SaveRequest $request, HouseholdTransaction $transaction): JsonResponse
    {
        $form = $request->validated();
        Gate::authorize('update', $transaction);

        $transaction->update([
            'household_category_id' => $form['categoryId'],
            'record_date' => $form['date'],
            'memo' => $form['memo'],
            'amount' => $form['amount'],
        ]);

        return response()->json([
            'message' => 'Household entry updated successfully.',
            'data' => new TransactionResource($transaction),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HouseholdTransaction $transaction): JsonResponse
    {
        Gate::authorize('delete', $transaction);
        $transaction->delete();

        return response()->json([
            'message' => 'Household entry deleted successfully.',
        ]);
    }
}
