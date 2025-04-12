<?php

namespace App\ValueObjects;

use App\Data\Inputs\InputsData;
use Brick\Math\RoundingMode;
use Brick\Money\Money;

class DownPayment
{
    protected Money $tcp;
    protected float $percent;

    public function __construct(float $tcp, float $percent)
    {
        $this->tcp = Money::of($tcp, 'PHP');
        $this->percent = $percent;
    }

    public static function fromInputs(InputsData $inputs): self
    {
        $tcp = $inputs->loanable->total_contract_price->inclusive()->getAmount()->toFloat();
        $percent = $inputs->loanable->down_payment->percent_dp?->value() ?? 0.0;

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
