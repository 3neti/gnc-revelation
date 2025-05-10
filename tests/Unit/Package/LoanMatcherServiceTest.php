<?php

use LBHurtado\Mortgage\Classes\{Buyer, LendingInstitution, Order, Property};
use LBHurtado\Mortgage\Services\{LoanMatcherService, BorrowingRulesService, AgeService};
use LBHurtado\Mortgage\Data\Match\MatchResultData;
use LBHurtado\Mortgage\ValueObjects\Percent;

beforeEach(function () {
    $this->rules = new BorrowingRulesService(new AgeService());
    $this->service = new LoanMatcherService();
});

dataset('loan matcher unique records', [
    'hdmf 1.0M by 49yo ₱17k gmi — expect not qualify' => [
        'hdmf', 1_000_000, 49, 17_000, false
    ],
    'hdmf 1.0M by 47yo ₱21k gmi — expect qualify' => [
        'hdmf', 1_000_000, 47, 21_000, true
    ],
    'hdmf 1.1M by 48yo ₱19k gmi — expect not qualify' => [
        'hdmf', 1_100_000, 48, 19_000, false
    ],
    'hdmf 1.2M by 47yo ₱21k gmi — expect not qualify' => [
        'hdmf', 1_200_000, 47, 21_000, false
    ],
    'rcbc 1.0M by 49yo ₱17k gmi — expect not qualify' => [
        'rcbc', 1_000_000, 49, 17_000, false
    ],
    'rcbc 1.1M by 48yo ₱19k gmi — expect qualify' => [
        'rcbc', 1_100_000, 45, 24_000, true
    ],
    'rcbc 1.4M by 45yo ₱25k gmi — expect not qualify' => [
        'rcbc', 1_400_000, 45, 25_000, false
    ],
]);

it('matches buyer qualification for given property', function (
    string $lender,
    float $tcp,
    int $age,
    float $income,
    bool $expected
) {
    $buyer = app(Buyer::class)
        ->setAge($age)
        ->setMonthlyGrossIncome($income)
    ;

    $property = (new Property($tcp))
        ->setInterestRate(Percent::ofPercent(6.25))
        ->setLendingInstitution(new LendingInstitution($lender));

    $result = $this->service->match($buyer, collect([$property]))->first();

    expect($result)->toBeInstanceOf(MatchResultData::class)
        ->and($result->qualified)->toBe($expected);

    echo "\n→ {$lender} @ ₱{$tcp} | Age: {$age} | Income: ₱{$income} → " .
        ($result->qualified ? '✅ Qualified' : '❌ Not Qualified');
})->with('loan matcher unique records');
