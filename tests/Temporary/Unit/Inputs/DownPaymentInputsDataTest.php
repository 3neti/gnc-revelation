<?php

use LBHurtado\Mortgage\Classes\{Order};
use LBHurtado\Mortgage\Classes\Buyer;
use LBHurtado\Mortgage\Classes\Property;
use LBHurtado\Mortgage\Data\Inputs\DownPaymentInputsData;

test('default down payment input', function () {
    $buyer = app(Buyer::class);
    $order = app(Order::class);
    $property = app(Property::class);
    $down_payment_input = DownPaymentInputsData::fromBooking($buyer, $property, $order);
    expect($down_payment_input)->toBeInstanceOf(DownPaymentInputsData::class)
        ->and($down_payment_input->percent_dp)->toBeNull()
        ->and($down_payment_input->dp_term)->toBeNull();
});
