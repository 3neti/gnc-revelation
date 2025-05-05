<?php

namespace LBHurtado\Mortgage\Calculators;

use LBHurtado\Mortgage\Factories\CalculatorFactory;
use LBHurtado\Mortgage\Attributes\CalculatorFor;
use LBHurtado\Mortgage\Enums\CalculatorType;

#[CalculatorFor(CalculatorType::LOAN_QUALIFICATION)]
class LoanQualificationCalculator extends BaseCalculator
{
    public function calculate(): bool
    {
        $monthly_amortization = CalculatorFactory::make(CalculatorType::AMORTIZATION, $this->inputs)->total();
        $disposable_income = CalculatorFactory::make(CalculatorType::DISPOSABLE_INCOME, $this->inputs)->calculate();

        return $disposable_income->isGreaterThanOrEqualTo($monthly_amortization);
    }
}
