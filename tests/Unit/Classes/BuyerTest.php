<?php

use Brick\Money\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use LBHurtado\Mortgage\Classes\Buyer;
use LBHurtado\Mortgage\Classes\LendingInstitution;
use LBHurtado\Mortgage\Classes\Property;
use LBHurtado\Mortgage\Data\QualificationComputationData;
use LBHurtado\Mortgage\Enums\Property\DevelopmentType;
use LBHurtado\Mortgage\Exceptions\MaximumBorrowingAgeBreached;
use LBHurtado\Mortgage\Exceptions\MinimumBorrowingAgeNotMet;
use LBHurtado\Mortgage\Services\BorrowingRulesService;
use LBHurtado\Mortgage\ValueObjects\Percent;
use Whitecube\Price\Price;

it('initializes with default values', function () {
    // Arrange & Act
    $buyer = app(Buyer::class);

    // Assert
    expect($buyer->getBirthdate())->toBeInstanceOf(Carbon::class)
        ->and($buyer->getBirthdate()->isSameDay(Carbon::parse(config('gnc-revelation.defaults.buyer.birthdate'))))->toBeTrue()
        ->and($buyer->getMonthlyGrossIncome())->toBeInstanceOf(Price::class)
        ->and($buyer->getMonthlyGrossIncome()->inclusive()->getAmount()->toFloat())->toBe(15000.00)
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
    $income = 25_000;
    $buyer = app(Buyer::class);

    // Act
    $buyer->setMonthlyGrossIncome($income);

    // Assert
    expect($buyer->getMonthlyGrossIncome()->inclusive()->getAmount()->toFloat())->toBe(25000.00);
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
    $buyer->setBirthdate(Carbon::parse('1999-03-17'));

    // Act
    $age = $buyer->getAge();

    // Assert
    expect($age)->toBeFloat()
        ->and($age)->toBeGreaterThan(26); // depends on current date
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
    expect($institution)->toBeInstanceOf(\LBHurtado\Mortgage\Classes\LendingInstitution::class)
        ->and($institution->key())->toBe(config('gnc-revelation.default_lending_institution'));
});

it('can change lending institution', function () {
    // Arrange
    $buyer = app(Buyer::class);
    $cbc = new \LBHurtado\Mortgage\Classes\LendingInstitution('cbc');

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
        ->setLendingInstitution(new \LBHurtado\Mortgage\Classes\LendingInstitution($institution))
        ->setOverrideMaximumPayingAge($overridePayingAge);

    expect($buyer->getLendingInstitution()->maximumPayingAge())->toBe($expectedPayingAge)
        ->and($buyer->getLendingInstitution()->offset())->toBe($offset)
        ->and($buyer->getMaximumTermAllowed())->toBe($expectedTerm);
})->with('institution ages');

it('calculates disposable income using default multiplier', function () {
    $buyer = app(Buyer::class);
    $buyer->setIncomeRequirementMultiplier(0.35);//TODO: should this be a default?
    $income = $buyer->getMonthlyDisposableIncome();

    expect($income->inclusive()->getAmount()->toFloat())->toBeGreaterThan(0)
        ->and($income->inclusive()->getAmount()->toFloat())
        ->toEqual(ceil(15000 * 0.35))
    ; // default config value
});

it('calculates disposable income using custom multiplier', function () {
    $buyer = app(Buyer::class)->setIncomeRequirementMultiplier(0.4);
    $income = $buyer->getMonthlyDisposableIncome();

    expect($income->inclusive()->getAmount()->toFloat())
        ->toEqual(ceil(15000 * 0.4));
});

it('can add other sources of income to gross monthly income', function () {
    $buyer = app(Buyer::class)
        ->addOtherSourcesOfIncome('side hustle', 2000)
        ->addOtherSourcesOfIncome('spouse', Money::of(5000, 'PHP'));

    $gross = $buyer->getMonthlyGrossIncome()->inclusive()->getAmount()->toFloat();

    expect($gross)->toBeGreaterThan(15000)
        ->and($gross)->toEqual(15000 + 2000 + 5000); // default + modifiers
});

it('calculates joint monthly disposable income including co-borrowers', function () {
    // Arrange: Main borrower
    $main = app(Buyer::class);
    $main->setIncomeRequirementMultiplier(0.4);

    // Add other income sources
    $main->addOtherSourcesOfIncome('main business', 5000); // +5k

    // Co-borrower 1
    $co1 = app(Buyer::class)
        ->setIncomeRequirementMultiplier(0.35)
        ->setMonthlyGrossIncome(new Price(Money::of(10000, 'PHP')));

    // Co-borrower 2
    $co2 = app(Buyer::class)
        ->setIncomeRequirementMultiplier(0.4)
        ->setMonthlyGrossIncome(new Price(Money::of(15000, 'PHP')));

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
        ->setLendingInstitution(new \LBHurtado\Mortgage\Classes\LendingInstitution('hdmf'))
        ->setOverrideMaximumPayingAge(70); // max term = 30

    // Co-borrower 1: age 50 → max term 20
    $co1 = app(Buyer::class)
        ->setAge(50)
        ->setLendingInstitution(new \LBHurtado\Mortgage\Classes\LendingInstitution('hdmf'))
        ->setOverrideMaximumPayingAge(70);

    // Co-borrower 2: age 45 → max term 25
    $co2 = app(Buyer::class)
        ->setAge(45)
        ->setLendingInstitution(new \LBHurtado\Mortgage\Classes\LendingInstitution('hdmf'))
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


/** 1 */
it('validates if joint income can afford monthly amortization', function () {
    $buyer = app(Buyer::class)
        ->setIncomeRequirementMultiplier(0.4)
        ->addOtherSourcesOfIncome('Side Hustle', 8000);

    $co = app(Buyer::class)
        ->setAge(30)
        ->setMonthlyGrossIncome(new Price(Money::of(15000, 'PHP')))
        ->setIncomeRequirementMultiplier(0.4);

    $buyer->addCoBorrower($co);

    // Use the actual Property class instead of a fake
    $property = new Property(1_000_000, DevelopmentType::BP_957);

    // You may optionally set interest or other values here
    $property->setInterestRate(0.06);

    expect($buyer->getQualificationComputation($property)->qualifies())->toBeTrue();
});

dataset('affordability cases', function () {
    return [
//         [loanable, interest, borrower_income, co_borrower_income, expected]
        'can afford easily'           => [1000000, 0.06, 20000, 15000, true],
        'just enough to afford'      => [1000000, 0.06, 12000, 10000, true],
        'barely not enough'          => [1000000, 0.06, 4000, 1900, false],
        'not enough'                 => [1000000, 0.06, 3000, 2000, false],
        'can’t afford high loan'     => [5000000, 0.07, 15000, 10000, false],
        'low interest helps'         => [1000000, 0.03, 10000, 10000, true],
        'short term helps'           => [1000000, 0.06, 10000, 10000, true],
        'fails due to buffer margin' => [1000000, 0.06, 4000, 2100, false],
    ];
});

it('validates joint income against monthly amortization', function (
    float $loanable,
    float $interest,
    float $main_income,
    float $co_income,
    bool $expected
) {
    // Disable config defaults that may interfere
    config()->set('gnc-revelation.defaults.buyer.gross_monthly_income', 0);
    config()->set('gnc-revelation.default_buffer_margin', 0.1); // default buffer

    // Arrange: Buyer and Co-Borrower
    $buyer = new Buyer(app(BorrowingRulesService::class));
    $buyer
        ->setAge(30)
        ->setIncomeRequirementMultiplier(1.0)
        ->setMonthlyGrossIncome($main_income)
        ->setLendingInstitution(new LendingInstitution('hdmf'));

    $co = new Buyer(app(BorrowingRulesService::class));
    $co
        ->setAge(30)
        ->setIncomeRequirementMultiplier(1.0)
        ->setMonthlyGrossIncome($co_income)
        ->setLendingInstitution(new LendingInstitution('hdmf'));

    $buyer->addCoBorrower($co);

    // Arrange: Property with forced loan and interest
    $property = new Property($loanable);
    $property->setPercentLoanableValue(Percent::ofPercent(100));
    $property->setInterestRate(Percent::ofFraction($interest));
    $property->setRequiredBufferMargin(0.1); // 10% buffer margin

    // Act & Assert
//    dump($buyer->getQualificationComputation($property));
    expect($buyer->getQualificationComputation($property)->qualifies())->toBe($expected);
})->with('affordability cases');

it('returns qualification gap if borrower cannot afford loan', function () {
    // Arrange: Borrower who cannot afford the amortization
    $buyer = app(Buyer::class)
        ->setIncomeRequirementMultiplier(1.0)
        ->setMonthlyGrossIncome(new Price(Money::of(2000, 'PHP')))
        ->setAge(30)
        ->setLendingInstitution(new \LBHurtado\Mortgage\Classes\LendingInstitution('hdmf'));

    // Arrange: Real property with forced loanable value and interest
    $property = new Property(1000000); // TCP = ₱1M
    $property->setPercentLoanableValue(Percent::ofPercent(100)); // Loanable = 100%
    $property->setInterestRate(Percent::ofFraction(0.06)); // 6% interest
    $property->setRequiredBufferMargin(0.1); // 10% buffer

    // Act
    $gap = $buyer->getQualificationComputation($property)
        ->gap()
        ->getAmount()
        ->toFloat();

    // Assert
    expect($gap)->toBeGreaterThan(0)
        ->and($buyer->getQualificationComputation($property)->failedMessage())->toBeString()
        ->and($buyer->getQualificationComputation($property)->failedMessage())->toContain('₱');
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

    $institution = new LendingInstitution($institutionKey);
    $buyer = new Buyer(app(\LBHurtado\Mortgage\Services\BorrowingRulesService::class));
    $buyer->setLendingInstitution($institution);

    $property = new Property(1_000_000);

    if (!is_null($propertyBuffer)) {
        $property->setRequiredBufferMargin($propertyBuffer);
    }

    // Act
    $resolved = $buyer->resolveBufferMargin($property);

    // Assert
    expect($resolved)->toEqual($expected);
})->with('buffer margin scenarios');

it('returns default down payment term from config', function () {
    config()->set('gnc-revelation.defaults.buyer.down_payment_term', 12);
    $buyer = app(Buyer::class);

    expect($buyer->getDownPaymentTerm())->toBe(12);
});

it('returns balance payment term based on joint max term', function () {
    $buyer = app(Buyer::class)->setAge(35);
    $co = app(Buyer::class)->setAge(45);

    $buyer->addCoBorrower($co);

    $expected = min(
        $buyer->getMaximumTermAllowed(),
        $co->getMaximumTermAllowed()
    );

    expect($buyer->getBalancePaymentTerm())->toBe($expected);
});

it('returns a structured qualification computation', function () {
    $buyer = app(Buyer::class)
        ->setMonthlyGrossIncome(20000)
        ->setIncomeRequirementMultiplier(0.4)
        ->setAge(30)
        ->setLendingInstitution(new LendingInstitution('hdmf'));

    $property = new Property(1_000_000);
    $property->setInterestRate(0.06);
    $property->setPercentLoanableValue(Percent::ofPercent(100));
    $property->setRequiredBufferMargin(0.1);

    $result = $buyer->getQualificationComputation($property);

    expect($result)->toBeInstanceOf(QualificationComputationData::class)
        ->and($result->monthlyPayment)->toBeInstanceOf(Money::class)
        ->and($result->required->isGreaterThan($result->monthlyPayment))->toBeTrue()
        ->and($result->actual->isPositive())->toBeTrue();
});

it('can determine qualification status and gap from result DTO', function () {
    $buyer = app(Buyer::class)
        ->setMonthlyGrossIncome(2000)
        ->setIncomeRequirementMultiplier(1.0)
        ->setAge(30)
        ->setLendingInstitution(new LendingInstitution('hdmf'));

    $property = new Property(1_000_000);
    $property->setInterestRate(0.06);
    $property->setPercentLoanableValue(Percent::ofPercent(100));
    $property->setRequiredBufferMargin(0.1);

    $result = $buyer->getQualificationComputation($property);

    expect($result->qualifies())->toBeFalse()
        ->and($result->gap()->isPositive())->toBeTrue()
        ->and($result->failedMessage())->toContain('₱');
});

it('returns zero gap and null message when borrower qualifies', function () {
    $buyer = app(Buyer::class)
        ->setMonthlyGrossIncome(50000)
        ->setIncomeRequirementMultiplier(0.5)
        ->setAge(30)
        ->setLendingInstitution(new LendingInstitution('hdmf'));

    $property = new Property(1_000_000);
    $property->setInterestRate(0.06);
    $property->setPercentLoanableValue(Percent::ofPercent(100));
    $property->setRequiredBufferMargin(0.1);

    $result = $buyer->getQualificationComputation($property);

    expect($result->qualifies())->toBeTrue()
        ->and($result->gap()->isZero())->toBeTrue()
        ->and($result->failedMessage())->toBeNull();
});

it('returns qualification gap using getQualificationGap()', function () {
    $buyer = app(Buyer::class)
        ->setMonthlyGrossIncome(3000)
        ->setIncomeRequirementMultiplier(1.0)
        ->setAge(30)
        ->setLendingInstitution(new \LBHurtado\Mortgage\Classes\LendingInstitution('hdmf'));

    $property = new Property(1_000_000);
    $property->setInterestRate(0.06);
    $property->setPercentLoanableValue(Percent::ofPercent(100));
    $property->setRequiredBufferMargin(0.1);

    // Act
    $gapFloat = QualificationComputationData::from($buyer, $property)->gap()->getAmount()->toFloat();

    // Use DTO to compare
    $gapViaDTO = QualificationComputationData::from($buyer, $property)->gap()->getAmount()->toFloat();

    expect($gapFloat)->toBeFloat()
        ->and($gapFloat)->toBeGreaterThan(0)
        ->and($gapFloat)->toEqual($gapViaDTO);
});
