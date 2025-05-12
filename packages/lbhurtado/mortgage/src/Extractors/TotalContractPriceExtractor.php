<?php

namespace LBHurtado\Mortgage\Extractors;

use LBHurtado\Mortgage\Attributes\ExtractorFor;
use LBHurtado\Mortgage\Enums\ExtractorType;
use Whitecube\Price\Price;

#[ExtractorFor(ExtractorType::TOTAL_CONTRACT_PRICE)]
class TotalContractPriceExtractor extends BaseExtractor
{
    public function extract(): Price
    {
        return $this->inputs->order()->getTotalContractPrice() ?? $this->inputs->property()->getTotalContractPrice();
    }

    public function toFloat(): float
    {
        return $this->extract()->inclusive()->getAmount()->toFloat();
    }

}
