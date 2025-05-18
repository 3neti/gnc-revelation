<?php

namespace LBHurtado\Mortgage\Factories;

use LBHurtado\Mortgage\Classes\{Buyer, Order, Property, LendingInstitution};
use LBHurtado\Mortgage\Data\Inputs\InputsData;
use LBHurtado\Mortgage\ValueObjects\Percent;
use LBHurtado\Mortgage\Enums\MonthlyFee;
use Illuminate\Support\Arr;

class InputsDataFactory
{
    public static function fromArray(array $data): InputsData
    {
        $lending_institution = Arr::get($data, 'lending_institution');
        $total_contract_price = Arr::get($data, 'total_contract_price');
        $age = Arr::get($data, 'buyer.age');
        $monthly_gross_income = Arr::get($data, 'buyer.monthly_income');
        $co_borrower_age = Arr::get($data, 'co_borrower.age');
        $co_borrower_income = Arr::get($data, 'co_borrower.monthly_income');
        $additional_income = Arr::get($data, 'buyer.additional_income');
        $balance_payment_interest = Arr::get($data, 'balance_payment_interest');
        $percent_down_payment = Arr::get($data, 'percent_down_payment');
        $percent_miscellaneous_fee = Arr::get($data, 'percent_miscellaneous_fee');
        $processing_fee = Arr::get($data, 'processing_fee');
        $add_mri = Arr::get($data, 'add_mri');
        $add_fi = Arr::get($data, 'add_fi');

        return self::from(
            $lending_institution,
            $total_contract_price,
            $age,
            $monthly_gross_income,
            $co_borrower_age,
            $co_borrower_income,
            $additional_income,
            $balance_payment_interest,
            $percent_down_payment,
            $percent_miscellaneous_fee,
            $processing_fee,
            $add_mri,
            $add_fi
        );
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
    ): InputsData
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

        return InputsData::fromBooking($buyer, $property, $order);
    }
}
