<?php

namespace LBHurtado\Mortgage\Classes;

use LBHurtado\Mortgage\ValueObjects\Percent;
use LBHurtado\Mortgage\Services\AgeService;
use Illuminate\Support\{Arr, Carbon};
use InvalidArgumentException;

class LendingInstitution
{
    protected string $key;

    protected int $offset;

    public function __construct(?string $key = null)
    {
        $key ??= config('gnc-revelation.default_lending_institution', 'hdmf');

        if (!in_array($key, self::keys())) {
            throw new InvalidArgumentException("Invalid lending institution key: {$key}");
        }

        $this->key = $key;
    }

    public static function keys(): array
    {
        return array_keys(config('gnc-revelation.lending_institutions', []));
    }

    public function key(): string
    {
        return $this->key;
    }

    public function record(): array
    {
        return config("gnc-revelation.lending_institutions.{$this->key}");
    }

    public function get(string $path, mixed $default = null): mixed
    {
        return Arr::get($this->record(), $path, $default);
    }

    public function name(): string
    {
        return $this->get('name');
    }

    public function alias(): string
    {
        return $this->get('alias');
    }

    public function type(): string
    {
        return $this->get('type');
    }

    public function minimumAge(): int
    {
        return $this->get('borrowing_age.minimum');
    }

    public function maximumAge(): int
    {
        return $this->get('borrowing_age.maximum');
    }

    public function offset(): int
    {
        return $this->offset ?? $this->get('borrowing_age.offset', 0);
    }

    public function newOffset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    public function maximumTerm(): int
    {
        return $this->get('maximum_term');
    }

    public function maximumPayingAge(): int
    {
        return $this->get('maximum_paying_age');
    }

    public function maxAllowedTerm(Carbon $birthdate, ?int $overridePayingAge = null): int
    {
        $age = app(AgeService::class)->getAgeInFloat($birthdate);
        $limit = ($overridePayingAge ?? $this->maximumPayingAge()) + $this->offset();

        return min((int) floor($limit - $age), $this->maximumTerm());
    }

    public function getRequiredBufferMargin(): ?float
    {
        return $this->get('buffer_margin'); // optional config key
    }

    public function getIncomeRequirementMultiplier(): ?Percent
    {
        return Percent::ofFraction($this->get('income_requirement_multiplier'));
    }

    public function getInterestRate(): ?Percent
    {
        return Percent::ofFraction($this->get('interest_rate'));
    }

    public function getPercentDownPayment(): Percent
    {
        $default = $this->get('percent_dp') ?? 0.0;

        return Percent::ofFraction($default);
    }

    /** TODO: make the return Price */
    public function getLoanableValueMultiplier(): ?float
    {
        return $this->get('loanable_value_multiplier');
    }

    public function getPercentMiscellaneousFees(): Percent
    {
        $default = $this->get('percent_mf') ?? 0.0;

        return Percent::ofFraction($default);
    }
}
