<?php

use Tests\Fakes\{FakeProperty, FlexibleFakeProperty};
use App\Classes\LendingInstitution;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Whitecube\Price\Price;
use App\Classes\Buyer;
use Brick\Money\Money;

it('initializes with default values', function () {
    // Arrange & Act
    $buyer = app(Buyer::class);

    // Assert
    expect($buyer->getBirthdate())->toBeInstanceOf(Carbon::class)
        ->and($buyer->getBirthdate()->isSameDay(Carbon::parse(config('gnc-revelation.defaults.buyer.birthdate'))))->toBeTrue()
        ->and($buyer->getGrossMonthlyIncome())->toBeInstanceOf(Price::class)
        ->and($buyer->getGrossMonthlyIncome()->inclusive()->getAmount()->toFloat())->toBe(15000.00)
        ->and($buyer->isRegional())->toBeFalse()
        ->and($buyer->getCoBorrowers())->toBeInstanceOf(Collection::class)
        ->and($buyer->getCoBorrowers())->toHaveCount(0);
});

it('can set and get birthdate', function () {
    // Arrange
    $birthdate = Carbon::parse('1990-01-01');
    $buyer = app(Buyer::class);

    // Act
    $buyer->setBirthdate($birthdate);

    // Assert
    expect($buyer->getBirthdate()->isSameDay($birthdate))->toBeTrue();
});

it('can set and get gross monthly income', function () {
    // Arrange
    $income = new Price(Money::of(25000, 'PHP'));
    $buyer = app(Buyer::class);

    // Act
    $buyer->setGrossMonthlyIncome($income);

    // Assert
    expect($buyer->getGrossMonthlyIncome()->inclusive()->getAmount()->toFloat())->toBe(25000.00);
});

it('can set and get regional flag', function () {
    // Arrange
    $buyer = app(Buyer::class);

    // Act
    $buyer->setRegional(true);

    // Assert
    expect($buyer->isRegional())->toBeTrue();
});

it('can add co-borrowers', function () {
    // Arrange
    $mainBuyer = app(Buyer::class);
    $coBorrower = app(Buyer::class);

    // Act
    $mainBuyer->addCoBorrower($coBorrower);

    // Assert
    expect($mainBuyer->getCoBorrowers())->toHaveCount(1)
        ->and($mainBuyer->getCoBorrowers()->first())->toBe($coBorrower);
});

it('can set birthdate using age in years', function () {
    // Arrange
    $buyer = app(Buyer::class);

    // Act
    $buyer->setAge(40);

    // Assert
    expect($buyer->getBirthdate()->isSameDay(now()->subYears(40)))->toBeTrue();
});

it('can calculate age based on birthdate', function () {
    // Arrange
    $buyer = app(Buyer::class);
    $buyer->setBirthdate(Carbon::parse('1980-01-01'));

    // Act
    $age = $buyer->getAge();

    // Assert
    expect($age)->toBeFloat()
        ->and($age)->toBeGreaterThan(40); // depends on current date
});

it('can get the oldest between main and co-borrowers', function () {
    // Arrange
    $main = app(Buyer::class);
    $main->setBirthdate(Carbon::parse('1990-01-01'));

    $older = app(Buyer::class);
    $older->setBirthdate(Carbon::parse('1980-01-01'));

    $younger = app(Buyer::class);
    $younger->setBirthdate(Carbon::parse('2000-01-01'));

    $main->addCoBorrower($older)->addCoBorrower($younger);

    // Act
    $oldest = $main->getOldestAmongst();

    // Assert
    expect($oldest)->toBe($older);
});
//
use App\Exceptions\MinimumBorrowingAgeNotMet;
use App\Exceptions\MaximumBorrowingAgeBreached;

it('throws exception if age is below minimum', function () {
    // Arrange
    $buyer = app(Buyer::class);
    $tooYoung = now()->subYears(Buyer::getMinimumBorrowingAge() - 1);

    // Assert
    $this->expectException(MinimumBorrowingAgeNotMet::class);

    // Act
    $buyer->setBirthdate($tooYoung);
});

it('throws exception if age exceeds maximum', function () {
    // Arrange
    $buyer = app(Buyer::class);
    $tooOld = now()->subYears(Buyer::getMaximumBorrowingAge() + 1);

    // Assert
    $this->expectException(MaximumBorrowingAgeBreached::class);

    // Act
    $buyer->setBirthdate($tooOld);
});

it('initializes with default lending institution', function () {
    // Arrange & Act
    $buyer = app(Buyer::class);
    $institution = $buyer->getLendingInstitution();

    // Assert
    expect($institution)->toBeInstanceOf(\App\Classes\LendingInstitution::class)
        ->and($institution->key())->toBe(config('gnc-revelation.default_lending_institution'));
});

it('can change lending institution', function () {
    // Arrange
    $buyer = app(Buyer::class);
    $cbc = new \App\Classes\LendingInstitution('cbc');

    // Act
    $buyer->setLendingInstitution($cbc);

    // Assert
    expect($buyer->getLendingInstitution()->key())->toBe('cbc');
});

dataset('institution ages', function () {
    return [
        ['hdmf', 25, 70, 70, 0, 30],
        ['hdmf', 25, 70, 65, 0, 30],
        ['rcbc', 25, 65, 65, -1, 20],
        ['hdmf', 40, 70, 70, 0, 30],
        ['hdmf', 40, 70, 65, 0, 25],
        ['rcbc', 40, 65, 65, -1, 20],
        ['hdmf', 45, 70, 70, 0, 25],
        ['hdmf', 45, 70, 65, 0, 20],
        ['rcbc', 45, 65, 65, -1, 19],
        ['hdmf', 50, 70, 70, 0, 20],
        ['hdmf', 50, 70, 65, 0, 15],
        ['rcbc', 50, 65, 65, -1, 14],
        ['hdmf', 55, 70, 70, 0, 15],
        ['hdmf', 55, 70, 65, 0, 10],
        ['rcbc', 55, 65, 65, -1, 9],
        ['hdmf', 60, 70, 70, 0, 10],
        ['hdmf', 60, 70, 65, 0, 5],
        ['rcbc', 60, 65, 65, -1, 4],
    ];
});

it('calculates the correct maximum term allowed based on age and institution', function (
    string $institution,
    int $age,
    int $expectedPayingAge,
    int $overridePayingAge,
    int $offset,
    int $expectedTerm
) {
    $buyer = app(Buyer::class)
        ->setAge($age)
        ->setLendingInstitution(new \App\Classes\LendingInstitution($institution))
        ->setOverrideMaximumPayingAge($overridePayingAge);

    expect($buyer->getLendingInstitution()->maximumPayingAge())->toBe($expectedPayingAge)
        ->and($buyer->getLendingInstitution()->offset())->toBe($offset)
        ->and($buyer->getMaximumTermAllowed())->toBe($expectedTerm);
})->with('institution ages');

it('calculates disposable income using default multiplier', function () {
    $buyer = app(Buyer::class);
    $income = $buyer->getMonthlyDisposableIncome();

    expect($income->inclusive()->getAmount()->toFloat())->toBeGreaterThan(0)
        ->and($income->inclusive()->getAmount()->toFloat())
        ->toEqual(ceil(15000 * 0.35)); // default config value
});

it('calculates disposable income using custom multiplier', function () {
    $buyer = app(Buyer::class)->setDisposableIncomeMultiplier(0.4);
    $income = $buyer->getMonthlyDisposableIncome();

    expect($income->inclusive()->getAmount()->toFloat())
        ->toEqual(ceil(15000 * 0.4));
});

it('can add other sources of income to gross monthly income', function () {
    $buyer = app(Buyer::class)
        ->addOtherSourcesOfIncome('side hustle', 2000)
        ->addOtherSourcesOfIncome('spouse', Money::of(5000, 'PHP'));

    $gross = $buyer->getGrossMonthlyIncome()->inclusive()->getAmount()->toFloat();

    expect($gross)->toBeGreaterThan(15000)
        ->and($gross)->toEqual(15000 + 2000 + 5000); // default + modifiers
});

it('calculates joint monthly disposable income including co-borrowers', function () {
    // Arrange: Main borrower
    $main = app(Buyer::class);
    $main->setDisposableIncomeMultiplier(0.4);

    // Add other income sources
    $main->addOtherSourcesOfIncome('main business', 5000); // +5k

    // Co-borrower 1
    $co1 = app(Buyer::class)
        ->setDisposableIncomeMultiplier(0.35)
        ->setGrossMonthlyIncome(new Price(Money::of(10000, 'PHP')));

    // Co-borrower 2
    $co2 = app(Buyer::class)
        ->setDisposableIncomeMultiplier(0.4)
        ->setGrossMonthlyIncome(new Price(Money::of(15000, 'PHP')));

    // Act
    $main->addCoBorrower($co1)->addCoBorrower($co2);
    $jointDisposable = $main->getJointMonthlyDisposableIncome();

    // Assert: Calculate expected result manually
    $expectedMain = ceil((15000 + 5000) * 0.4); // 8,000
    $expectedCo1 = ceil(10000 * 0.35);          // 3,500
    $expectedCo2 = ceil(15000 * 0.4);           // 6,000
    $expectedTotal = $expectedMain + $expectedCo1 + $expectedCo2; // 17,500

    expect($jointDisposable)->toBeInstanceOf(Price::class)
        ->and($jointDisposable->inclusive()->getAmount()->toFloat())->toEqual($expectedTotal);
});

it('can group other income sources by tag', function () {
    $buyer = app(Buyer::class)
        ->addOtherSourcesOfIncome('Spouse Salary', 5000, 'household')
        ->addOtherSourcesOfIncome('Rental Income', 3000, 'business')
        ->addOtherSourcesOfIncome('Side Gig', 2500, 'business');

    $breakdown = $buyer->getIncomeBreakdownByTag();

    expect($breakdown)->toMatchArray([
        'household' => 5000,
        'business' => 5500,
    ]);
});

it('can return formatted breakdown of income sources', function () {
    $buyer = app(Buyer::class)
        ->addOtherSourcesOfIncome('Spouse Salary', 5000, 'household')
        ->addOtherSourcesOfIncome('Side Gig', 3000, 'business');

    $formatted = $buyer->getFormattedIncomeBreakdown();

    expect($formatted)->toMatchArray([
        'household' => [['name' => 'Spouse Salary', 'amount' => 5000]],
        'business' => [['name' => 'Side Gig', 'amount' => 3000]],
    ]);
});

it('calculates joint maximum term allowed from oldest borrower', function () {
    // Main borrower: age 40 → max term 30
    $main = app(Buyer::class)
        ->setAge(40)
        ->setLendingInstitution(new \App\Classes\LendingInstitution('hdmf'))
        ->setOverrideMaximumPayingAge(70); // max term = 30

    // Co-borrower 1: age 50 → max term 20
    $co1 = app(Buyer::class)
        ->setAge(50)
        ->setLendingInstitution(new \App\Classes\LendingInstitution('hdmf'))
        ->setOverrideMaximumPayingAge(70);

    // Co-borrower 2: age 45 → max term 25
    $co2 = app(Buyer::class)
        ->setAge(45)
        ->setLendingInstitution(new \App\Classes\LendingInstitution('hdmf'))
        ->setOverrideMaximumPayingAge(70);

    $main->addCoBorrower($co1)->addCoBorrower($co2);

    $individualTerms = [
        'main' => $main->getMaximumTermAllowed(),
        'co1' => $co1->getMaximumTermAllowed(),
        'co2' => $co2->getMaximumTermAllowed(),
    ];

    $expectedJoint = collect($individualTerms)->min();

    expect($main->getJointMaximumTermAllowed())->toBe($expectedJoint);
});



it('validates if joint income can afford monthly amortization', function () {
    $buyer = app(Buyer::class)
        ->setDisposableIncomeMultiplier(0.4)
        ->addOtherSourcesOfIncome('Side Hustle', 8000);

    $co = app(Buyer::class)
        ->setAge(30)
        ->setGrossMonthlyIncome(new Price(Money::of(15000, 'PHP')))
        ->setDisposableIncomeMultiplier(0.4);

    $buyer->addCoBorrower($co);

    $property = new FakeProperty();

    expect($buyer->qualifiesFor($property))->toBeTrue();
});

dataset('affordability cases', function () {
    return [
        // [loanable, interest, borrower_income, co_borrower_income, expected]
        'can afford easily' => [1000000, 0.06, 20000, 15000, true],
        'just enough to afford' => [1000000, 0.06, 12000, 10000, true],
        'barely not enough' => [1000000, 0.06, 4000, 1900, false],
        'not enough' => [1000000, 0.06, 3000, 2000, false],
        'can’t afford high loan' => [5000000, 0.07, 15000, 10000, false],
        'low interest helps' => [1000000, 0.03, 10000, 10000, true],
        'short term helps' => [1000000, 0.06, 10000, 10000, true], // will override term later if needed
        'fails due to buffer margin' => [1000000, 0.06, 4000, 2100, false], // just enough before buffer, fails after
    ];
});

it('validates joint income against monthly amortization', function (
    float $loanable,
    float $interest,
    float $main_income,
    float $co_income,
    bool $expected
) {
    // Arrange
    $buyer = app(Buyer::class)
        ->setDisposableIncomeMultiplier(1)
        ->setGrossMonthlyIncome(new Price(Money::of($main_income, 'PHP')))
        ->setAge(30)
        ->setLendingInstitution(new \App\Classes\LendingInstitution('hdmf'));

    $co = app(Buyer::class)
        ->setDisposableIncomeMultiplier(1)
        ->setGrossMonthlyIncome(new Price(Money::of($co_income, 'PHP')))
        ->setAge(30)
        ->setLendingInstitution(new \App\Classes\LendingInstitution('hdmf'));

    $buyer->addCoBorrower($co);

    $property = new FlexibleFakeProperty($loanable, $interest);

    // Act & Assert
    expect($buyer->qualifiesFor($property))->toBe($expected);
})->with('affordability cases');

it('validates joint income against amortization with buffer margin', function (
    float $loanable,
    float $interest,
    float $main_income,
    float $co_income,
    bool $expected
) {
    $buyer = app(Buyer::class)
        ->setDisposableIncomeMultiplier(1)
        ->setGrossMonthlyIncome(new Price(Money::of($main_income, 'PHP')))
        ->setAge(30)
        ->setLendingInstitution(new \App\Classes\LendingInstitution('hdmf'));

    $co = app(Buyer::class)
        ->setDisposableIncomeMultiplier(1)
        ->setGrossMonthlyIncome(new Price(Money::of($co_income, 'PHP')))
        ->setAge(30)
        ->setLendingInstitution(new \App\Classes\LendingInstitution('hdmf'));

    $buyer->addCoBorrower($co);

    $property = new FlexibleFakeProperty($loanable, $interest);

    expect($buyer->qualifiesFor($property, 0.1))->toBe($expected); // 10% buffer
})->with('affordability cases');

it('returns qualification gap if borrower cannot afford loan', function () {
    $buyer = app(Buyer::class)
        ->setDisposableIncomeMultiplier(1)
        ->setGrossMonthlyIncome(new Price(Money::of(2000, 'PHP')))
        ->setAge(30)
        ->setLendingInstitution(new \App\Classes\LendingInstitution('hdmf'));

    $property = new FlexibleFakeProperty(1000000, 0.06); // ₱1M, 6% interest

    $gap = $buyer->getQualificationGap($property, 0.1);

    expect($gap)->toBeGreaterThan(0)
        ->and($buyer->failedQualificationMessage($property))->toBeString()
        ->and($buyer->failedQualificationMessage($property))->toContain('₱');
});

dataset('buffer margin scenarios', function () {
    return [
        // [propertyBuffer, institutionBuffer, fallbackConfigBuffer, expected]

        'property defines margin' => [0.15, null, 0.1, 0.15],
        'institution fallback' => [null, 0.10, 0.1, 0.10],
        'fallback to config default' => [null, null, 0.0, 0.0],
        'property disables buffer' => [0.0, 0.10, 0.1, 0.0],
    ];
});

it('resolves correct buffer margin from property, institution, or config', function (
    ?float $propertyBuffer,
    ?float $institutionBuffer,
    float $fallbackConfig,
    float $expected
) {
    // Arrange
    config()->set('gnc-revelation.default_buffer_margin', $fallbackConfig);

    $buyer = app(Buyer::class);
    $institutionKey = 'testbank';

    config()->set("gnc-revelation.lending_institutions.{$institutionKey}", [
        'name' => 'Test Bank',
        'alias' => 'TB',
        'type' => 'mock',
        'borrowing_age' => [
            'minimum' => 18,
            'maximum' => 65,
            'offset' => 0,
        ],
        'maximum_term' => 30,
        'maximum_paying_age' => 70,
        'buffer_margin' => $institutionBuffer,
    ]);

    $institution = new \App\Classes\LendingInstitution($institutionKey);
    $property = new \Tests\Fakes\FlexibleFakeProperty(1000000, 0.06, $propertyBuffer);

    $buyer->setLendingInstitution($institution);

    // Act
    $resolved = $buyer->resolveBufferMargin($property);

    // Assert
    expect($resolved)->toEqual($expected);
})->with('buffer margin scenarios');
