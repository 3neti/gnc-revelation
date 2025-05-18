<?php

namespace LBHurtado\Mortgage\ValueObjects;

use LBHurtado\Mortgage\Data\Inputs\MortgageParticulars;
use LBHurtado\Mortgage\Factories\ExtractorFactory;
use LBHurtado\Mortgage\Enums\ExtractorType;
use Brick\Math\RoundingMode;
use Brick\Money\Money;

class PaymentBreakdown
{
    protected Money $tcp;
    protected float $percent;

    public function __construct(float $tcp, float $percent)
    {
        $this->tcp = Money::of($tcp, 'PHP');
        $this->percent = $percent;
    }

    public static function fromInputs(MortgageParticulars $inputs): self
    {
        $tcp = ExtractorFactory::make(ExtractorType::TOTAL_CONTRACT_PRICE, $inputs)->toFloat();
        $percent = ExtractorFactory::make(ExtractorType::PERCENT_DOWN_PAYMENT, $inputs)->extract()->value();

        return new self($tcp, $percent);
    }

    public function amount(): Money
    {
        return $this->tcp->multipliedBy($this->percent, roundingMode: RoundingMode::HALF_UP);
    }

    public function loanable(): Money
    {
        return $this->tcp->minus($this->amount());
    }

    public function percent(): float
    {
        return $this->percent;
    }

    public function tcp(): Money
    {
        return $this->tcp;
    }
}
