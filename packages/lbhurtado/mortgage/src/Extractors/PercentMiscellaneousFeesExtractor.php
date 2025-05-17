<?php

namespace LBHurtado\Mortgage\Extractors;

use LBHurtado\Mortgage\Factories\ExtractorFactory;
use LBHurtado\Mortgage\Attributes\ExtractorFor;
use LBHurtado\Mortgage\ValueObjects\Percent;
use LBHurtado\Mortgage\Enums\ExtractorType;

#[ExtractorFor(ExtractorType::PERCENT_MISCELLANEOUS_FEES)]
class PercentMiscellaneousFeesExtractor extends BaseExtractor
{

    public function extract(): Percent
    {
        $lending_institution = ExtractorFactory::make(ExtractorType::LENDING_INSTITUTION, $this->inputs)->extract();

        return ($this->inputs->order()->getPercentMiscellaneousFees() ?? $this->inputs->property()->getPercentMiscellaneousFees())
            ?? $lending_institution->getPercentMiscellaneousFees();
    }

    public function toFloat(): float
    {
        return $this->extract()->value();
    }
}
