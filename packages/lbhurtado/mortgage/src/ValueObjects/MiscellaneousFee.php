<?php

namespace LBHurtado\Mortgage\ValueObjects;

use LBHurtado\Mortgage\Contracts\FeeRulesInterface;
use LBHurtado\Mortgage\Factories\ExtractorFactory;
use LBHurtado\Mortgage\Factories\FeeRulesFactory;
use LBHurtado\Mortgage\Data\Inputs\InputsData;
use LBHurtado\Mortgage\Enums\ExtractorType;
use Brick\Money\Money;

/** @deprecated  */
class MiscellaneousFee
{
    protected float $tcp;
    protected float $percent_mf;
    protected float $percent_dp;
    protected ?float $override_multiplier = null;

    public function __construct(float $tcp, float $percent_mf, float $percent_dp, ?float $override_multiplier = null)
    {
        $this->tcp = $tcp;
        $this->percent_mf = $percent_mf;
        $this->percent_dp = $percent_dp;
        $this->override_multiplier = $override_multiplier;
    }

    public static function fromInputs(InputsData $inputs, ?FeeRulesInterface $rules = null): self
    {
        $tcp = $inputs->loanable->total_contract_price->inclusive()->getAmount()->toFloat();
        $percent_mf = $inputs->fees->percent_mf?->value() ?? 0.0;
        $percent_dp = $inputs->loanable->down_payment->percent_dp?->value() ?? 0.0;
        $lending_institution = ExtractorFactory::make(ExtractorType::LENDING_INSTITUTION, $inputs)->extract();
        $rules ??= FeeRulesFactory::make(institution: $lending_institution);

        $override_multiplier = $rules->shouldApplyMiscellaneousFee($tcp)
            ? $rules->getPartialMiscellaneousFeeMultiplier($tcp, Percent::ofFraction($percent_dp))?->value()
            : null;

        return new self($tcp, $percent_mf, $percent_dp, $override_multiplier);
    }

    public function total(): Money
    {
        return Money::of($this->tcp * $this->percent_mf, 'PHP');
    }

    public function partial(): Money
    {
        return Money::of($this->tcp * $this->percent_mf * $this->override_multiplier, 'PHP');
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
