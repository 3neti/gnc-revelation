<?php

namespace LBHurtado\Mortgage\Factories;

use LBHurtado\Mortgage\Classes\{Buyer, Order, Property, LendingInstitution};
use LBHurtado\Mortgage\Data\Inputs\MortgageParticulars;
use LBHurtado\Mortgage\Data\Inputs\MortgageInputsData;
use LBHurtado\Mortgage\ValueObjects\Percent;
use LBHurtado\Mortgage\Enums\MonthlyFee;

class MortgageParticularsFactory
{
    public static function fromData(MortgageInputsData $data): MortgageParticulars
    {
        return self::fromArray($data->toArray());
    }

    public static function fromArray(array $data): MortgageParticulars
    {
        return self::from(...$data);
    }

    public static function from(
        string $lending_institution,
        float  $total_contract_price,
        int    $age,
        float  $monthly_gross_income,
        int    $co_borrower_age,
        float  $co_borrower_income,
        float  $additional_income,
        ?float $balance_payment_interest,
        ?float $percent_down_payment,
        ?float $percent_miscellaneous_fee,
        float  $processing_fee,
        bool   $add_mri,
        bool   $add_fi,
    ): MortgageParticulars
    {
        $buyer = app(Buyer::class)
            ->setAge($age)
            ->setMonthlyGrossIncome($monthly_gross_income)
            ->addOtherSourcesOfIncome('test', $additional_income)
        ;

        if ($co_borrower_age) {
            $co_borrower = app(Buyer::class)
                ->setAge($co_borrower_age)
                ->setMonthlyGrossIncome($co_borrower_income)
            ;
            $buyer->addCoBorrower($co_borrower);
        }

        $property = (new Property($total_contract_price))
            ->setLendingInstitution(new LendingInstitution($lending_institution))
        ;

        $order = new Order;

        if (!is_null($balance_payment_interest)) {
            $order->setInterestRate(Percent::ofFraction($balance_payment_interest));
        }

        if (!is_null($percent_miscellaneous_fee)) {
            $order->setPercentMiscellaneousFees(Percent::ofFraction($percent_miscellaneous_fee));
        }
        if (!is_null($processing_fee)) {
            $order->setProcessingFee($processing_fee);
        }
        if ($add_mri) {
            $order->addMonthlyFee(MonthlyFee::MRI);
        }
        if ($add_fi) {
            $order->addMonthlyFee(MonthlyFee::FIRE_INSURANCE);
        }
        if (!is_null($percent_down_payment)) {
            $order->setPercentDownPayment($percent_down_payment);
        }

        return MortgageParticulars::fromBooking($buyer, $property, $order);
    }
}
