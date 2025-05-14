<?php

namespace Database\Seeders;

use LBHurtado\Mortgage\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'sku' => 'product-a',
                'name' => 'Product A',
                'brand' => 'Brand A',
                'category' => 'Category Green',
                'description' => 'Product A description',
                'price' => 850_000,
                'properties' => [
                    [
                        'code' => 'property-1',
                        'name' => 'Property 1',
                        'total_contract_price' => 850_000,
                        'appraisal_value' => 850_000,
                        'lending_institution' => 'hdmf',
                    ],
                    [
                        'code' => 'property-2',
                        'name' => 'Property 2',
                        'total_contract_price' => 855_000,
                        'appraisal_value' => 850_000,
                        'lending_institution' => 'hdmf',
                    ],
                    [
                        'code' => 'property-3',
                        'name' => 'Property 3',
                        'total_contract_price' => 860_000,
                        'appraisal_value' => 850_000,
                        'lending_institution' => 'hdmf',
                    ],
                ],
            ],
            [
                'sku' => 'product-b',
                'name' => 'Product B',
                'brand' => 'Brand A',
                'category' => 'Category Yellow',
                'description' => 'Product B description',
                'price' => 1_250_000,
                'properties' => [
                    [
                        'code' => 'property-4',
                        'name' => 'Property 4',
                        'total_contract_price' => 1_300_000,
                        'appraisal_value' => 1_250_000,
                        'lending_institution' => 'rcbc',
                        'status' => 'available'
                    ],
                    [
                        'code' => 'property-5',
                        'name' => 'Property 5',
                        'total_contract_price' => 1_350_000,
                        'appraisal_value' => 1_250_000,
                        'lending_institution' => 'rcbc',
                        'status' => 'available'
                    ],
                    [
                        'code' => 'property-6',
                        'name' => 'Property 6',
                        'total_contract_price' => 1_400_000,
                        'appraisal_value' => 1_250_000,
                        'lending_institution' => 'rcbc',
                        'status' => 'available'
                    ],
                ],
            ],
            [
                'sku' => 'product-c',
                'name' => 'Product C',
                'brand' => 'Brand A',
                'category' => 'Category Yellow',
                'description' => 'Product C description',
                'price' => 2_500_000,
                'properties' => [
                    [
                        'code' => 'property-7',
                        'name' => 'Property 7',
                        'total_contract_price' => 2_500_000,
                        'appraisal_value' => 2_500_000,
                        'lending_institution' => 'cbc',
                        'status' => 'available'
                    ],
                    [
                        'code' => 'property-8',
                        'name' => 'Property 8',
                        'total_contract_price' => 2_550_000,
                        'appraisal_value' => 2_500_000,
                        'lending_institution' => 'cbc',
                        'status' => 'available'
                    ],
                    [
                        'code' => 'property-9',
                        'name' => 'Property 9',
                        'total_contract_price' => 2_600_000,
                        'appraisal_value' => 2_500_000,
                        'lending_institution' => 'cbc',
                        'status' => 'available'
                    ],
                ],
            ],
        ];

        foreach ($products as $productData) {
            // Extract properties from product data
            $properties = $productData['properties'];
            unset($productData['properties']); // Exclude properties to avoid mass assignment issues

            // Create the product
            $product = Product::create($productData);

            // Create related properties
            foreach ($properties as $propertyData) {
                $product->properties()->create($propertyData);
            }
        }
    }
}
