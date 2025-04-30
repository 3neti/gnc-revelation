<?php

namespace App\Classes;

use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use LBHurtado\Mortgage\Classes\Buyer;
use LBHurtado\Mortgage\Classes\LendingInstitution;
use LBHurtado\Mortgage\Contracts\BuyerInterface;
use LBHurtado\Mortgage\Contracts\PropertyInterface;
use LBHurtado\Mortgage\Exceptions\BirthdateNotSet;
use LBHurtado\Mortgage\Factories\MoneyFactory;
use LBHurtado\Mortgage\Modifiers\{OtherIncomeModifier};
use LBHurtado\Mortgage\Modifiers\DisposableModifier;
use LBHurtado\Mortgage\Services\BorrowingRulesService;
use LBHurtado\Mortgage\Traits\HasFinancialAttributes;
use LBHurtado\Mortgage\ValueObjects\Percent;
use Whitecube\Price\Price;

/** @deprecated  */
class BuyerOldButWorking implements BuyerInterface
{
    use HasFinancialAttributes;

    protected Carbon $birthdate;
    protected Price $gross_monthly_income;
    protected bool $regional;
    protected Collection $co_borrowers;
    protected LendingInstitution $lendingInstitution;
    protected ?int $override_maximum_paying_age = null;
    protected ?Percent $disposable_income_multiplier = null;
    protected array $other_income_sources = [];

    public function __construct(
        protected BorrowingRulesService $rules
    ) {
        $this->birthdate = Carbon::parse(config('gnc-revelation.defaults.buyer.birthdate', now()->subYears(30)));
        $this->gross_monthly_income = new Price(Money::of(config('gnc-revelation.defaults.buyer.gross_monthly_income'), 'PHP'));
        $this->regional = config('gnc-revelation.defaults.buyer.regional', false);
        $this->co_borrowers = collect();
        $this->lendingInstitution = new LendingInstitution();
    }

    public static function getMinimumBorrowingAge(): int
    {
        return config('gnc-revelation.limits.min_borrowing_age', 21);
    }

    public static function getMaximumBorrowingAge(): int
    {
        return config('gnc-revelation.limits.max_borrowing_age', 65);
    }

    public function getBirthdate(): Carbon
    {
        return $this->birthdate;
    }

    public function setBirthdate(Carbon $value): static
    {
        $this->rules->validateBirthdate($value);
        $this->birthdate = $value;

        return $this;
    }

    public function getMonthlyGrossIncome(): Price
    {
        return $this->gross_monthly_income;
    }

    public function setGrossMonthlyIncome(Price|Money|float $income): static
    {
        $this->gross_monthly_income =
            $income instanceof Price ? $income
                : ($income instanceof Money ? new Price($income)
                : MoneyFactory::price($income));

        return $this;
    }

    public function isRegional(): bool
    {
        return $this->regional;
    }

    public function setRegional(bool $regional): static
    {
        $this->regional = $regional;
        return $this;
    }

    public function getCoBorrowers(): Collection
    {
        return $this->co_borrowers;
    }

    public function setCoBorrowers(Collection $co_borrowers): static
    {
        $this->co_borrowers = $co_borrowers;
        return $this;
    }

    public function addCoBorrower(Buyer $co_borrower): static
    {
        $this->co_borrowers->push($co_borrower);
        return $this;
    }

    public function setAge(int $years): static
    {
        $birthdate = Carbon::now()->subYears($years);
        $this->setBirthdate($birthdate);

        return $this;
    }


    public function getAge(): float
    {
        if (!isset($this->birthdate)) {
            throw new BirthdateNotSet("Birthdate must be set before getting age.");
        }

        return $this->rules->calculateAge($this->birthdate);
    }

    public function getOldestAmongst(): Buyer
    {
        $oldest = $this;

        $this->co_borrowers->each(function (Buyer $co_borrower) use (&$oldest) {
            if ($co_borrower->getBirthdate()->lt($oldest->getBirthdate())) {
                $oldest = $co_borrower;
            }
        });

        return $oldest;
    }

    public function getLendingInstitution(): LendingInstitution
    {
        return $this->lendingInstitution;
    }

    public function setLendingInstitution(LendingInstitution $institution): static
    {
        $this->lendingInstitution = $institution;
        return $this;
    }

    public function setOverrideMaximumPayingAge(?int $age): static
    {
        $this->override_maximum_paying_age = $age;
        return $this;
    }

    public function getOverrideMaximumPayingAge(): ?int
    {
        return $this->override_maximum_paying_age;
    }

    public function getMaximumTermAllowed(): int
    {
        return $this->lendingInstitution->maxAllowedTerm($this->getBirthdate(), $this->getOverrideMaximumPayingAge());
    }

    public function getJointMaximumTermAllowed(): int
    {
        $terms = collect([$this->getMaximumTermAllowed()]);

        $this->co_borrowers->each(function (Buyer $co_borrower) use ($terms) {
            $terms->push($co_borrower->getMaximumTermAllowed());
        });

        return $terms->min();
    }

    public function getDisposableIncomeMultiplier(): ?Percent
    {
        return $this->disposable_income_multiplier;
    }

    public function setDisposableIncomeMultiplier(Percent|float|int|null $value): static
    {
        $this->disposable_income_multiplier = match (true) {
            $value instanceof Percent       => $value,
            is_int($value)                  => Percent::ofPercent($value),
            $value === 1.0                  => Percent::ofPercent(100), // special case for clarity
            is_float($value) && $value <= 1 => Percent::ofFraction($value),
            is_float($value)                => Percent::ofPercent($value),
            is_null($value)                 => null,
            default                         => throw new \InvalidArgumentException("Invalid value for disposable income multiplier."),
        };

        return $this;
    }

    public function getMonthlyDisposableIncome(): Price
    {
        return (new Price($this->getMonthlyGrossIncome()->inclusive()))
            ->addModifier('disposable income multiplier', DisposableModifier::class, $this);
    }

    public function addOtherSourcesOfIncome(string $name, Money|float $value, string $tag = 'unclassified'): static
    {
        $money = $value instanceof Money ? $value : Money::of($value, 'PHP');

        // Add to Price as modifier
        $this->gross_monthly_income->addModifier(
            $name,
            OtherIncomeModifier::class,
            $money
        );

        // Track metadata
        $this->other_income_sources[] = [
            'name' => $name,
            'amount' => $money->getAmount()->toFloat(),
            'money' => $money,
            'tag' => $tag,
        ];

        return $this;
    }

    public function getRawIncomeSources(): array
    {
        return $this->other_income_sources;
    }

    public function getIncomeBreakdownByTag(): array
    {
        return collect($this->other_income_sources)
            ->groupBy('tag')
            ->map(fn ($items) =>
            collect($items)->sum('amount')
            )
            ->all();
    }

    public function getFormattedIncomeBreakdown(): array
    {
        return collect($this->other_income_sources)
            ->groupBy('tag')
            ->map(fn ($items) =>
            $items->map(fn ($source) => [
                'name' => $source['name'],
                'amount' => $source['money']->getAmount()->toFloat(),
            ])->all()
            )
            ->all();
    }

    public function getJointMonthlyDisposableIncome(): Price
    {
        $total = new Price($this->getMonthlyDisposableIncome()->inclusive());
//        dd('getJointMonthlyDisposableIncome', $total->inclusive()->getAmount()->toFloat());
        $this->co_borrowers->each(function (Buyer $co_borrower) use ($total) {
            $total->addModifier(
                'co-borrower: ' . $co_borrower->getBirthdate()->toDateString(),
                $co_borrower->getMonthlyDisposableIncome()->inclusive(),
                roundingMode: RoundingMode::CEILING
            );
        });

        return $total;
    }

    public function qualifiesFor(PropertyInterface $property): bool
    {
        $buffer = $this->resolveBufferMargin($property);

        $loanable = $property->getLoanableAmount()->inclusive()->getAmount()->toFloat();
        $interest = $property->getInterestRate()->value(); // FIX: Extract value

        $termYears = $this->getJointMaximumTermAllowed();

        $monthlyInterestRate = $interest / 12;

        $numberOfMonths = $termYears * 12;

        $monthlyPayment = ($loanable * $monthlyInterestRate) / (1 - pow(1 + $monthlyInterestRate, -$numberOfMonths));

        $required = $monthlyPayment * (1 + $buffer);

        $actual = $this->getJointMonthlyDisposableIncome()->inclusive()->getAmount()->toFloat();

        return $actual >= $required;
    }

    public function getQualificationGap(PropertyInterface $property): float
    {
        $buffer = $this->resolveBufferMargin($property);
        $loanable = $property->getLoanableAmount()->inclusive()->getAmount()->toFloat();
        $interest = $property->getInterestRate()->value();
        $termYears = $this->getJointMaximumTermAllowed();

        $monthlyInterestRate = $interest / 12;
        $numberOfMonths = $termYears * 12;

        $monthlyPayment = ($loanable * $monthlyInterestRate) / (1 - pow(1 + $monthlyInterestRate, -$numberOfMonths));
        $required = $monthlyPayment * (1 + $buffer);

        $actual = $this->getJointMonthlyDisposableIncome()->inclusive()->getAmount()->toFloat();

        return max(0, round($required - $actual, 2)); // return 0 if they already qualify
    }

    public function failedQualificationMessage(PropertyInterface $property, float $buffer = 0.1): ?string
    {
        $gap = $this->getQualificationGap($property, $buffer);

        return $gap > 0
            ? "You need at least ₱" . number_format($gap, 2) . " more in joint disposable income to qualify."
            : null;
    }

    public function resolveBufferMargin(PropertyInterface $property): float
    {
        if (method_exists($property, 'getRequiredBufferMargin') && $property->getRequiredBufferMargin() !== null) {
            return $property->getRequiredBufferMargin()->value(); // <- cast to float
        }

        $institutionBuffer = $this->lendingInstitution->getRequiredBufferMargin();
        if ($institutionBuffer !== null) {
            return $institutionBuffer->value(); // <- cast to float
        }

        return (float) config('gnc-revelation.default_buffer_margin', 0.1);
    }

//    public function resolveBufferMargin(PropertyInterface $property): float
//    {
//        if (method_exists($property, 'getRequiredBufferMargin') && $property->getRequiredBufferMargin() !== null) {
//            return $property->getRequiredBufferMargin();
//        }
//
//        $institutionBuffer = $this->lendingInstitution->getRequiredBufferMargin();
//        if ($institutionBuffer !== null) {
//            return $institutionBuffer;
//        }
//
//        return config('gnc-revelation.default_buffer_margin', 0.1);
//    }

    public function getDownPaymentTerm(): ?int
    {
        return config('gnc-revelation.defaults.buyer.down_payment_term');
    }

    public function getBalancePaymentTerm(): ?int
    {
        return $this->getJointMaximumTermAllowed();
    }

    public function getQualificationComputation(PropertyInterface $property): array
    {
        $buffer = $this->resolveBufferMargin($property);
        $loanable = $property->getLoanableAmount()->inclusive()->getAmount()->toFloat();
        $interest = $property->getInterestRate()->value();
        $termYears = $this->getJointMaximumTermAllowed();

        $monthlyInterestRate = $interest / 12;
        $numberOfMonths = $termYears * 12;

        $monthlyPayment = ($loanable * $monthlyInterestRate) / (1 - pow(1 + $monthlyInterestRate, -$numberOfMonths));
        $required = $monthlyPayment * (1 + $buffer);
        $actual = $this->getJointMonthlyDisposableIncome()->inclusive()->getAmount()->toFloat();

        return compact('loanable', 'interest', 'termYears', 'monthlyInterestRate', 'monthlyPayment', 'required', 'actual', 'buffer');
    }
}
