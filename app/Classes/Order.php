<?php

namespace App\Classes;

use App\Support\Traits\HasFinancialAttributes;
use App\Contracts\OrderInterface;
use App\Support\MoneyFactory;
use App\ValueObjects\FeeCollection;
use App\ValueObjects\Percent;
use App\Enums\Order\MonthlyFee;
use Whitecube\Price\Price;
use Brick\Money\Money;

class Order implements OrderInterface
{
    use HasFinancialAttributes;

    protected ?Percent $percentDownPayment = null;
    protected FeeCollection $monthlyFees;
    protected ?Price $discountAmount = null;
    protected ?Price $lowCashOut = null;
    protected ?Price $consultingFee = null;
    protected ?Price $processingFee = null;
    protected ?Price $waivedProcessingFee = null;
    protected ?int $dpTerm = null;
    protected ?int $bpTerm = null;
    protected ?LendingInstitution $lendingInstitution = null;
    protected ?Price $tcp = null;

    public function __construct()
    {
        $this->monthlyFees = new FeeCollection();
    }

    public function setPercentDownPayment(Percent|float|int $value): static
    {
        if ((is_numeric($value) && $value < 0) || ($value instanceof Percent && $value->value() < 0)) {
            throw new \InvalidArgumentException("Down payment percent must not be negative.");
        }

        $this->percentDownPayment = match (true) {
            $value instanceof Percent       => $value,
            is_int($value)                  => Percent::ofPercent($value),
            is_float($value) && $value <= 1 => Percent::ofFraction($value),
            is_float($value)                => Percent::ofPercent($value),
            default                         => throw new \InvalidArgumentException("Unsupported value for percent down payment"),
        };

        return $this;
    }

    public function getPercentDownPayment(): ?Percent
    {
        return $this->percentDownPayment;
    }

    public function setLendingInstitution(LendingInstitution $institution): static
    {
        $this->lendingInstitution = $institution;
        return $this;
    }

    public function getLendingInstitution(): ?LendingInstitution
    {
        return $this->lendingInstitution;
    }


    public function setTotalContractPrice(float|Price $value): static
    {
        $this->tcp = $value instanceof Price
            ? $value
            : MoneyFactory::priceWithPrecision($value);

        return $this;
    }

    public function getTotalContractPrice(): ?Price
    {
        return $this->tcp;
    }

    public function addMonthlyFee(MonthlyFee $type, ?Price $amount = null): static
    {
        $price = $amount
            ?: match (true) {
                $this->tcp instanceof Price && $this->lendingInstitution instanceof LendingInstitution
                => $type->computeFromTCP(
                    $this->tcp->inclusive()->getAmount()->toFloat(),
                    $this->lendingInstitution
                ),
                default => throw new \LogicException("TCP and Lending Institution must be set before computing a monthly fee."),
            };

        $this->monthlyFees->addAddOn($type->label(), $price->inclusive());

        return $this;
    }

    public function setMonthlyFee(MonthlyFee $type, float $value): static
    {
        $this->monthlyFees->addAddOn($type->label(), $value);
        return $this;
    }

    public function getMonthlyFee(MonthlyFee $type): ?float
    {
        return $this->monthlyFees
            ->allAddOns()
            ->get($type->label())?->getAmount()
            ?->toFloat();
    }

    public function getMonthlyFees(): FeeCollection
    {
        return $this->monthlyFees;
    }

    public function setDiscountAmount(?float $value): static
    {
        $this->discountAmount = $value ? MoneyFactory::priceWithPrecision($value) : null;
        return $this;
    }

    public function getDiscountAmount(): ?Price
    {
        return $this->discountAmount;
    }

    public function setLowCashOut(?float $value): static
    {
        $this->lowCashOut = $value ? MoneyFactory::priceWithPrecision($value) : null;
        return $this;
    }

    public function getLowCashOut(): ?Price
    {
        return $this->lowCashOut;
    }

    public function setConsultingFee(?float $value): static
    {
        $this->consultingFee = $value ? MoneyFactory::priceWithPrecision($value) : null;
        return $this;
    }

    public function getConsultingFee(): ?Price
    {
        return $this->consultingFee;
    }

    public function setProcessingFee(?float $value): static
    {
        $this->processingFee = $value ? MoneyFactory::priceWithPrecision($value) : null;
        return $this;
    }

    public function getProcessingFee(): ?Price
    {
        return $this->processingFee;
    }

    public function setWaivedProcessingFee(?float $value): static
    {
        $this->waivedProcessingFee = $value ? MoneyFactory::priceWithPrecision($value) : null;
        return $this;
    }

    public function getWaivedProcessingFee(): ?Price
    {
        return $this->waivedProcessingFee;
    }

    public function setDownPaymentTerm(?int $months): static
    {
        $this->dpTerm = $months;
        return $this;
    }

    public function getDownPaymentTerm(): ?int
    {
        return $this->dpTerm;
    }

    public function setBalancePaymentTerm(?int $years): static
    {
        $this->bpTerm = $years;
        return $this;
    }

    public function getBalancePaymentTerm(): ?int
    {
        return $this->bpTerm;
    }
}
