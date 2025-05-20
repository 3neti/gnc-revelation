<?php

namespace LBHurtado\Mortgage\Data\Match;

use LBHurtado\Mortgage\Casts\PriceCast;
use LBHurtado\Mortgage\Data\Transformers\PriceToFloatTransformer;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Whitecube\Price\Price;

/** @deprecated  */
class MatchResultData extends Data
{
    public function __construct(
        public bool   $qualified,
        public string $product_code,
        #[WithTransformer(PriceToFloatTransformer::class)]
        #[WithCast(PriceCast::class)]
        public Price  $monthly_amortization,
        #[WithTransformer(PriceToFloatTransformer::class)]
        #[WithCast(PriceCast::class)]
        public Price  $income_required,
        #[WithTransformer(PriceToFloatTransformer::class)]
        #[WithCast(PriceCast::class)]
        public Price  $required_equity,
        #[WithTransformer(PriceToFloatTransformer::class)]
        #[WithCast(PriceCast::class)]
        public Price  $income_gap,
        public string $reason
    ) {}
}
