<?php

use App\ValueObjects\FeeCollection;
use App\Enums\Order\MonthlyFee;
use App\ValueObjects\Percent;
use App\Classes\Order;

it('initializes with no values set', function () {
    $order = new Order();

    expect($order->getPercentDownPayment())->toBeNull()
        ->and($order->getMonthlyFee(MonthlyFee::MRI))->toBeNull()
        ->and($order->getMonthlyFee(MonthlyFee::FIRE_INSURANCE))->toBeNull()
        ->and($order->getMonthlyFees())->toBeInstanceOf(FeeCollection::class)
        ->and($order->getDiscountAmount())->toBeNull()
        ->and($order->getLowCashOut())->toBeNull()
        ->and($order->getConsultingFee())->toBeNull()
        ->and($order->getProcessingFee())->toBeNull()
        ->and($order->getWaivedProcessingFee())->toBeNull()
        ->and($order->getDownPaymentTerm())->toBeNull()
        ->and($order->getBalancePaymentTerm())->toBeNull();
});

it('can set and get percent down payment', function () {
    $order = new Order();

    $order->setPercentDownPayment(15);
    expect($order->getPercentDownPayment())->toEqualPercent(0.15);

    $order->setPercentDownPayment(0.10);
    expect($order->getPercentDownPayment())->toEqualPercent(0.10);

    $order->setPercentDownPayment(Percent::ofPercent(20));
    expect($order->getPercentDownPayment())->toEqualPercent(0.20);
});

it('can set discount, cash out, consulting and processing fees', function () {
    $order = new Order();

    $order->setDiscountAmount(10_000)
        ->setLowCashOut(5_000)
        ->setConsultingFee(7_500)
        ->setProcessingFee(12_000)
        ->setWaivedProcessingFee(3_000);

    expect($order->getDiscountAmount()->inclusive()->getAmount()->toFloat())->toEqual(10000.0)
        ->and($order->getLowCashOut()->inclusive()->getAmount()->toFloat())->toEqual(5000.0)
        ->and($order->getConsultingFee()->inclusive()->getAmount()->toFloat())->toEqual(7500.0)
        ->and($order->getProcessingFee()->inclusive()->getAmount()->toFloat())->toEqual(12000.0)
        ->and($order->getWaivedProcessingFee()->inclusive()->getAmount()->toFloat())->toEqual(3000.0);
});

it('can set down payment and balance payment terms', function () {
    $order = new Order();

    $order->setDownPaymentTerm(12)
        ->setBalancePaymentTerm(20);

    expect($order->getDownPaymentTerm())->toBe(12)
        ->and($order->getBalancePaymentTerm())->toBe(20);
});

it('throws exception when setting negative percent down payment', function () {
    $order = new Order();

    $this->expectException(\InvalidArgumentException::class);
    $order->setPercentDownPayment(-10);
});

it('can set and get monthly MRI and Fire Insurance explicitly', function () {
    $order = new Order();

    $order->addMonthlyFee(MonthlyFee::MRI, 800.0)
        ->addMonthlyFee(MonthlyFee::FIRE_INSURANCE, 250.0);

    expect($order->getMonthlyFee(MonthlyFee::MRI))->toEqual(800.0)
        ->and($order->getMonthlyFee(MonthlyFee::FIRE_INSURANCE))->toEqual(250.0);
});

it('returns null for unset monthly fees', function () {
    $order = new Order();

    expect($order->getMonthlyFee(MonthlyFee::MRI))->toBeNull()
        ->and($order->getMonthlyFee(MonthlyFee::FIRE_INSURANCE))->toBeNull();
});

it('can retrieve full FeeCollection of monthly fees', function () {
    $order = new Order();

    $order->addMonthlyFee(MonthlyFee::MRI, 500.0)
        ->addMonthlyFee(MonthlyFee::FIRE_INSURANCE, 200.0);

    $collection = $order->getMonthlyFees();

    expect($collection->totalAddOns()->getAmount()->toFloat())->toEqual(700.0)
        ->and($collection->allAddOns()->has(MonthlyFee::MRI->label()))->toBeTrue()
        ->and($collection->allAddOns()->has(MonthlyFee::FIRE_INSURANCE->label()))->toBeTrue();
});

it('uses default monthly fee value when no amount is passed', function () {
    $order = new Order();
    $order->addMonthlyFee(MonthlyFee::MRI);

    $expected = MonthlyFee::MRI->defaultAmount()->getAmount()->toFloat();

    expect($order->getMonthlyFee(MonthlyFee::MRI))->toEqual($expected);
});
