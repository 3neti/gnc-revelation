<?php

namespace LBHurtado\Mortgage\Http\Controllers;

use LBHurtado\Mortgage\Data\Models\ProductData;
use Illuminate\Http\{JsonResponse, Request};
use Spatie\LaravelData\DataCollection;
use LBHurtado\Mortgage\Models\Product;
use Illuminate\Routing\Controller;

class ProductController extends Controller
{
    /**
     * List all products filtered by lending institution.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Get lending institution from session or request
        $lendingInstitution = $request->session()->get('lending_institution');

        // Filter products based on lending institution
        $products = Product::with('properties')
            ->when($lendingInstitution, function ($query) use ($lendingInstitution) {
                $query->forLendingInstitution($lendingInstitution);
            })
            ->when($request->filled('sku'), function ($query) use ($request) {
                $query->where('sku', $request->input('sku'));
            })

            ->get()
        ;

        return response()->json((new DataCollection(ProductData::class, $products))->toArray());
    }

    /**
     * Show a specific product filtered by lending institution.
     *
     * @param  Request  $request
     * @param  string  $sku
     * @return JsonResponse
     */
    public function show(Request $request, string $sku): JsonResponse
    {
        // Get lending institution from session or request
        $lendingInstitution = $request->session()->get('lending_institution');

        // Filter specific product based on lending institution
        $product = Product::with('properties')
            ->when($lendingInstitution, function ($query) use ($lendingInstitution) {
                $query->forLendingInstitution($lendingInstitution);
            })
            ->where('sku', $sku)
            ->first();

        if (! $product) {
            return response()->json([
                'message' => "Product '{$sku}' not found for the given lending institution."
            ], 404);
        }

        return response()->json(ProductData::fromModel($product)->toArray());
    }

}
