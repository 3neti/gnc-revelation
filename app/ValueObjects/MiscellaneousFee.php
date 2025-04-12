<?php

namespace App\ValueObjects;

use App\Data\Inputs\InputsData;
use Brick\Money\Money;

class MiscellaneousFee
{
    protected float $tcp;
    protected float $percentMf;
    protected float $percentDp;
    protected float $multiplier;

    /** TODO: $overrideMultiplier is policy, change either in config or InputsData */
    public function __construct(float $tcp, float $percentMf, float $percentDp, ?float $overrideMultiplier = 0.0)
    {
        $this->tcp = $tcp;
        $this->percentMf = $percentMf;
        $this->percentDp = $percentDp;
        $this->multiplier = $overrideMultiplier;
    }

    public static function fromInputs(InputsData $inputs): self
    {
        $tcp = $inputs->loanable->total_contract_price->inclusive()->getAmount()->toFloat();
        $percentMf = $inputs->fees->percent_mf?->value() ?? 0.0;
        $percentDp = $inputs->loanable->down_payment->percent_dp?->value() ?? 0.0;

        return new self($tcp, $percentMf, $percentDp);
    }

    public function total(): Money
    {
        return Money::of($this->tcp * $this->percentMf, 'PHP');
    }

    public function partial(): Money
    {
        return Money::of($this->tcp * $this->percentMf * $this->multiplier, 'PHP');
    }

    public function balance(): Money
    {
        return $this->total()->minus($this->partial());
    }

    public function all(): array
    {
        return [
            'total' => $this->total(),
            'partial' => $this->partial(),
            'balance' => $this->balance(),
        ];
    }
}
