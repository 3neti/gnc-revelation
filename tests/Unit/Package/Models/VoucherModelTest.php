<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use FrittenKeeZ\Vouchers\Facades\Vouchers;
use FrittenKeeZ\Vouchers\Models\Voucher;

uses(RefreshDatabase::class);

test('voucher works', function () {
    $voucher = $voucher = Vouchers::create();
    expect($voucher)->toBeInstanceOf(Voucher::class);
    expect($voucher->code)->toBeString();
});
