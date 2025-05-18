<?php

namespace App\Services;

use App\Data\{BalancePaymentScheduleData,
    CashOutScheduleData,
    LoanProcessingData,
    ProductMatchData,
    QualificationResultData,
    RemediationStrategiesData};
use LBHurtado\Mortgage\Contracts\{OrderInterface};
use LBHurtado\Mortgage\Contracts\BuyerInterface;
use LBHurtado\Mortgage\Contracts\PropertyInterface;
use LBHurtado\Mortgage\Data\Inputs\MortgageParticulars;
use LBHurtado\Mortgage\Factories\MoneyFactory;

final class LoanProcessingService
{
    public function __construct(
        public BuyerInterface $buyer,
        public PropertyInterface $property,
        public OrderInterface $order
    ) {}

    public static function from(BuyerInterface $buyer, PropertyInterface $property, OrderInterface $order): self
    {
        return new self($buyer, $property, $order);
    }

    public function generate(): LoanProcessingData
    {
        $inputs = MortgageParticulars::fromBooking($this->buyer, $this->property, $this->order);
        $mortgage = new MortgageComputation($inputs);

        $qualification = $mortgage->getQualificationResult();

        return new LoanProcessingData(
            qualification: $qualification,
            cash_out_schedule: $this->generateCashOutSchedule($qualification),
            balance_payment_schedule: $this->generateBalanceSchedule($qualification),
            product_match: $this->attemptProductMatch($qualification),
            remediation: $this->suggestRemediation($qualification),
        );
    }

    protected function generateCashOutSchedule(QualificationResultData $q): CashOutScheduleData
    {
        $monthlyDp = $q->actual_down_payment / $this->order->getDownPaymentTerm();

        return new CashOutScheduleData(
            total_cash_out: $q->required_cash_out,
            downpayment: MoneyFactory::priceWithPrecision($q->actual_down_payment),
            dp_term_months: $this->order->getDownPaymentTerm(),
            monthly_dp_payment: MoneyFactory::priceWithPrecision($monthlyDp),
        );
    }

    protected function generateBalanceSchedule(QualificationResultData $q): BalancePaymentScheduleData
    {
        return new BalancePaymentScheduleData(
            monthly_amortization: $q->monthly_amortization,
            term_in_months: $this->order->getBalancePaymentTerm() * 12,
            interest_rate: $this->order->getInterestRate(),
        );
    }

    protected function attemptProductMatch(QualificationResultData $q): ProductMatchData
    {
        // Dummy matching logic for now
        return new ProductMatchData(
            matched: $q->qualifies,
            product_id: $q->qualifies ? 'HDMF_BASIC_001' : null,
            product_name: $q->qualifies ? 'Pag-IBIG Basic Loan' : null
        );
    }

    protected function suggestRemediation(QualificationResultData $q): RemediationStrategiesData
    {
        return new RemediationStrategiesData(
            add_income_sources: ! $q->qualifies,
            add_coborrower: ! $q->qualifies,
            switch_to_cheaper_property: ! $q->qualifies,
        );
    }
}
