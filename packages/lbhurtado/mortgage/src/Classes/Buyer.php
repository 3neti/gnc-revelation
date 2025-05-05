<?php

namespace LBHurtado\Mortgage\Classes;

use LBHurtado\Mortgage\Traits\{BuyerExtension, HasFinancialAttributes};
use LBHurtado\Mortgage\Services\BorrowingRulesService;
use LBHurtado\Mortgage\Modifiers\DisposableModifier;
use LBHurtado\Mortgage\Exceptions\BirthdateNotSet;
use LBHurtado\Mortgage\Contracts\BuyerInterface;
use LBHurtado\Mortgage\Factories\MoneyFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Whitecube\Price\Price;
use Brick\Money\Money;

class Buyer implements BuyerInterface
{
    use HasFinancialAttributes;
    use BuyerExtension;

    protected Carbon $birthdate;
    protected Price $monthly_gross_income;
    protected bool $regional;
    protected Collection $co_borrowers;
    protected LendingInstitution $lendingInstitution;
    protected ?int $override_maximum_paying_age = null;
    protected array $other_income_sources = [];

    public function __construct(
        protected BorrowingRulesService $rules
    ) {
        $this->birthdate = Carbon::parse(config('gnc-revelation.defaults.buyer.birthdate', now()->subYears(30)));
        $this->monthly_gross_income = new Price(Money::of(config('gnc-revelation.defaults.buyer.gross_monthly_income'), 'PHP'));
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
        return $this->monthly_gross_income;
    }

    public function setMonthlyGrossIncome(Price|Money|float $income): static
    {
        $this->monthly_gross_income =
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
        if ($this->getLendingInstitution() !== null) {
            $co_borrower->setLendingInstitution($this->getLendingInstitution());
        }
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
        $this->setIncomeRequirementMultiplier($this->lendingInstitution->getIncomeRequirementMultiplier());

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

    public function getMonthlyDisposableIncome(): Price
    {
        return (new Price($this->getMonthlyGrossIncome()->inclusive()))
            ->addModifier('disposable income multiplier', DisposableModifier::class, $this);
    }
}
