<?php

namespace App\Http\Controllers\Household;

use App\Http\Controllers\Controller;
use App\Http\Requests\Household\Categories\IndexRequest;
use App\Models\HouseholdCategory;
use Illuminate\Http\JsonResponse;

class CategoriesController extends Controller
{
    public function index(IndexRequest $request): JsonResponse
    {
        $type = $request->validated('type');
        $categories = [];

        if (isset($type)) {
            $categories = HouseholdCategory::query()
                ->where('type', $type)
                ->orderBy('id')
                ->get(['id', 'name', 'icon']);
        } else {
            $type = 'all';
            $categories = HouseholdCategory::query()
                ->orderBy('id')
                ->get(['id', 'name', 'icon']);
        }

        return response()->json([
            'data' => [
                'type' => $type,
                'categories' => $categories,
            ],
        ]);
    }
}
