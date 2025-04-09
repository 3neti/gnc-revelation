<?php

use App\Data\Inputs\DownPaymentInputsData;
use App\Classes\{Buyer, Order, Property};

test('default down payment input', function () {
    $buyer = app(Buyer::class);
    $order = app(Order::class);
    $property = app(Property::class);
    $down_payment_input = DownPaymentInputsData::fromBooking($buyer, $property, $order);
    expect($down_payment_input)->toBeInstanceOf(DownPaymentInputsData::class)
        ->and($down_payment_input->percent_dp)->toBeNull()
        ->and($down_payment_input->dp_term)->toBeNull();
});
