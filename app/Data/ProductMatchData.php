<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class ProductMatchData extends Data
{
    public function __construct(
        public bool $matched,
        public ?string $product_id = null,
        public ?string $product_name = null,
    ) {}
}
