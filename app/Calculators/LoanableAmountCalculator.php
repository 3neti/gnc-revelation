<?php

namespace App\Calculators;

use App\ValueObjects\DownPayment;
use App\ValueObjects\FeeCollection;
use App\ValueObjects\MiscellaneousFee;
use App\Attributes\CalculatorFor;
use App\Enums\CalculatorType;
use App\Support\MoneyFactory;
use Whitecube\Price\Price;

#[CalculatorFor(CalculatorType::LOANABLE_AMOUNT)]
final class LoanableAmountCalculator extends BaseCalculator
{
    public function calculate(): Price
    {
        $tcp = $this->inputs->loanable->total_contract_price->inclusive()->getAmount()->toFloat();
        $dpPercent = $this->inputs->loanable->down_payment->percent_dp?->value() ?? 0.0;
        $mfPercent = $this->inputs->fees->percent_mf?->value() ?? 0.0;

        // DownPayment VO
        $downPayment = new DownPayment($tcp, $dpPercent);
        $loanable = $downPayment->loanable(); // Money object

        // MiscellaneousFee VO
        $mf = new MiscellaneousFee($tcp, $mfPercent, $dpPercent);

        // FeeCollection with only the balance portion of MF
        $fees = new FeeCollection(addOns: [
            'balance miscellaneous fee' => $mf->balance()->getAmount()->toFloat(),
        ]);
//        dd($loanable->getAmount()->toFloat(), $mf->partial()->getAmount()->toFloat(), $loanable->plus($fees->totalAddOns())->getAmount()->toFloat());
        return MoneyFactory::priceWithPrecision($loanable->plus($fees->totalAddOns()));
    }
}
