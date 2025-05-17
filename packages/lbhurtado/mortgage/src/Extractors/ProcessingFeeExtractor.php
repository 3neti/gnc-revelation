<?php

namespace LBHurtado\Mortgage\Extractors;

use LBHurtado\Mortgage\Attributes\ExtractorFor;
use LBHurtado\Mortgage\Enums\ExtractorType;
use Whitecube\Price\Price;

#[ExtractorFor(ExtractorType::PROCESSING_FEE)]
class ProcessingFeeExtractor extends BaseExtractor
{
    public function extract(): Price
    {
        return $this->inputs->order()->getProcessingFee() ?? $this->inputs->property()->getProcessingFee();
    }

    public function toFloat(): float
    {
        return $this->extract()->inclusive()->getAmount()->toFloat();
    }
}
