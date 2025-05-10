<?php

namespace LBHurtado\Mortgage\Calculators;

use LBHurtado\Mortgage\Factories\ExtractorFactory;
use LBHurtado\Mortgage\Attributes\CalculatorFor;
use LBHurtado\Mortgage\Enums\CalculatorType;
use LBHurtado\Mortgage\Enums\ExtractorType;

#[CalculatorFor(CalculatorType::BALANCE_PAYMENT_TERM)]
class BalancePaymentTermCalculator extends BaseCalculator
{
    public function calculate(): int
    {
        $oldest = $this->inputs->buyer()->getOldestAmongst();
        $lending_institution = ExtractorFactory::make(ExtractorType::LENDING_INSTITUTION, $this->inputs)->extract();

        return  $lending_institution->maxAllowedTerm($oldest->getBirthdate(), $oldest->getOverrideMaximumPayingAge());
    }
}
