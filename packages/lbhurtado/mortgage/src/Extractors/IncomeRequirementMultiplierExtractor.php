<?php

namespace LBHurtado\Mortgage\Extractors;

use LBHurtado\Mortgage\Attributes\ExtractorFor;
use LBHurtado\Mortgage\ValueObjects\Percent;
use LBHurtado\Mortgage\Enums\ExtractorType;

#[ExtractorFor(ExtractorType::INCOME_REQUIREMENT_MULTIPLIER)]
class IncomeRequirementMultiplierExtractor extends BaseExtractor
{
    public function extract(): Percent
    {
        //TODO: put order and property income requirement multiplier here

        return ($this->inputs->buyer()->getIncomeRequirementMultiplier() ?? $this->inputs->buyer()->getLendingInstitution()?->getIncomeRequirementMultiplier())
            ?? $this->inputs->property()->getIncomeRequirementMultiplier()
            ;
    }

    public function value(): float
    {
        return $this->extract()->value();
    }
}
