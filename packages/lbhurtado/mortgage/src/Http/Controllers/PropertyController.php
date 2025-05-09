<?php

namespace LBHurtado\Mortgage\Http\Controllers;

use LBHurtado\Mortgage\Data\Models\PropertyData;
use LBHurtado\Mortgage\Models\Property;
use Spatie\LaravelData\DataCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PropertyController
{
    public function index(Request $request): JsonResponse
    {
        $query = Property::query();

        // Optional filter by code
        if ($request->filled('code')) {
            $query->where('code', $request->string('code'));
        }

        // Optional filter by availability
        if ($request->boolean('available_only')) {
            $query->where('status', 'available');
        }

        // Use withMeta for meta filtering
        if ($request->filled('min_price')) {
            $query->withMeta('total_contract_price', '>=', (int) $request->min_price * 100);
        }

        if ($request->filled('max_price')) {
            $query->withMeta('total_contract_price', '<=', (int) $request->max_price * 100);
        }

        $properties = $query->get();

        return response()->json((new DataCollection(PropertyData::class, $properties))->toArray());
    }
}
