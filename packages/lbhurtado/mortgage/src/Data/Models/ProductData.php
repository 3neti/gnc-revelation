<?php

namespace LBHurtado\Mortgage\Data\Models;

use LBHurtado\Mortgage\Data\Transformers\PriceToFloatTransformer;
use Spatie\LaravelData\Attributes\WithTransformer;
use LBHurtado\Mortgage\Models\Product;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Data;
use Whitecube\Price\Price;

class ProductData extends Data
{
    public function __construct(
        public string $sku,
        public string $name,
        public string $brand,
        public string $category,
        public ?string $description,
        #[WithTransformer(PriceToFloatTransformer::class)]
        public Price $price,
        /** @var PropertyData[] */
        public DataCollection $properties,
    ) {}

    public static function fromModel(Product $product): self
    {
        $properties = new DataCollection(PropertyData::class, $product->properties);

        return new self(
            sku: $product->sku,
            name: $product->name,
            brand: $product->brand,
            category: $product->category,
            description: $product->description,
            price: $product->price,
            properties: $properties
        );
    }
}
