<?php

use Illuminate\Support\Carbon;
use LBHurtado\Mortgage\Classes\LendingInstitution;

it('can retrieve lending institution details from config', function () {
    $li = new LendingInstitution('rcbc');

    expect($li->key())->toBe('rcbc')
        ->and($li->name())->toBe('Rizal Commercial Banking Corporation')
        ->and($li->alias())->toBe('RCBC')
        ->and($li->type())->toBe('universal bank')
        ->and($li->minimumAge())->toBe(18)
        ->and($li->maximumAge())->toBe(60)
        ->and($li->offset())->toBe(-1)
        ->and($li->maximumTerm())->toBe(20)
        ->and($li->maximumPayingAge())->toBe(65);
});

it('throws exception for invalid institution key', function () {
    new LendingInstitution('invalid-key');
})->throws(InvalidArgumentException::class);

it('can compute max allowed term based on birthdate', function () {
    $li = new LendingInstitution('hdmf');
    $birthdate = Carbon::parse('1995-01-01');

    $term = $li->maxAllowedTerm($birthdate);

    expect($term)->toBeInt()
        ->and($term)->toBeLessThanOrEqual($li->maximumTerm());
});
