<?php

namespace LBHurtado\Mortgage\Http\Controllers;

use LBHurtado\Mortgage\Data\Payloads\MortgageResultPayload;
use LBHurtado\Mortgage\Classes\{Buyer, Order, Property};
use LBHurtado\Mortgage\Data\QualificationResultData;
use LBHurtado\Mortgage\Classes\LendingInstitution;
use LBHurtado\Mortgage\Data\MortgageResultData;
use LBHurtado\Mortgage\Data\Inputs\InputsData;
use LBHurtado\Mortgage\ValueObjects\Percent;
use LBHurtado\Mortgage\Enums\MonthlyFee;
use Illuminate\Http\Request;

class MortgageComputationController
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'lending_institution' => 'required|string|in:hdmf,rcbc',
            'total_contract_price' => 'required|numeric|min:100000',
            'buyer.age' => 'required|integer|min:18|max:65',
            'buyer.monthly_income' => 'required|numeric|min:1000',
            'buyer.additional_income' => 'nullable|numeric|min:0',
            'co_borrower.age' => 'nullable|integer|min:18|max:65',
            'co_borrower.monthly_income' => 'nullable|numeric|min:1000',
            'percent_down_payment' => 'nullable|numeric|min:0|max:1',
            'percent_miscellaneous_fee' => 'nullable|numeric|min:0|max:1',
            'processing_fee' => 'nullable|numeric|min:0',
            'add_mri' => 'boolean',
            'add_fi' => 'boolean',
        ]);

        $buyer = app(Buyer::class)
            ->setAge($data['buyer']['age'])
            ->setMonthlyGrossIncome($data['buyer']['monthly_income'])
            ->setLendingInstitution(new LendingInstitution($data['lending_institution']))
            ->addOtherSourcesOfIncome('user', $data['buyer']['additional_income'] ?? 0);

        if (!empty($data['co_borrower']['age'])) {
            $coBorrower = app(Buyer::class)
                ->setAge($data['co_borrower']['age'])
                ->setMonthlyGrossIncome($data['co_borrower']['monthly_income']);
            $buyer->addCoBorrower($coBorrower);
        }

        $property = new Property($data['total_contract_price']);

        $order = (new Order())
            ->setInterestRate(Percent::ofFraction(0.0625)) // optionally allow override
            ->setPercentMiscellaneousFees(Percent::ofFraction($data['percent_miscellaneous_fee'] ?? 0))
            ->setProcessingFee($data['processing_fee'] ?? 0)
            ->setLendingInstitution(new LendingInstitution($data['lending_institution']))
            ->setTotalContractPrice($data['total_contract_price']);

        if (isset($data['percent_down_payment'])) {
            $order->setPercentDownPayment($data['percent_down_payment']);
        }

        if ($data['add_mri'] ?? false) {
            $order->addMonthlyFee(MonthlyFee::MRI);
        }

        if ($data['add_fi'] ?? false) {
            $order->addMonthlyFee(MonthlyFee::FIRE_INSURANCE);
        }

        $inputs = InputsData::fromBooking($buyer, $property, $order);

        $result = MortgageResultPayload::fromResult(MortgageResultData::fromInputs($inputs));
//        dd('asdads');
        $qualification = QualificationResultData::fromInputs($inputs);

        return response()->json([
            'payload' => $result->toArray(),
            'qualification' => [
                'income_gap' => $qualification->income_gap->inclusive()->getAmount()->toFloat(),
                'loan_difference' => $qualification->loan_difference->inclusive()->getAmount()->toFloat(),
                'suggested_down_payment_percent' => $qualification->suggested_down_payment_percent->value(),
                'qualifies' => $qualification->qualifies,
                'reason' => $qualification->reason,
                'mortgage' => [ // Partial extraction for now
                    'monthly_amortization' => $qualification->mortgage->monthly_amortization->inclusive()->getAmount()->toFloat(),
                    'term_years' => $qualification->mortgage->balance_payment_term,
                ],
            ],
        ]);
    }
}
