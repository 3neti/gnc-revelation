<?php

namespace LBHurtado\Mortgage\Extractors;

use LBHurtado\Mortgage\Attributes\ExtractorFor;
use LBHurtado\Mortgage\ValueObjects\Percent;
use LBHurtado\Mortgage\Enums\ExtractorType;

#[ExtractorFor(ExtractorType::PERCENT_DOWN_PAYMENT)]
class PercentDownPaymentExtractor extends BaseExtractor
{
    public function extract(): Percent
    {
        return $this->inputs->order()->getPercentDownPayment() ?? $this->inputs->property()->getPercentDownPayment();
    }

    public function toFloat(): float
    {
        return $this->extract()->value();
    }
}
