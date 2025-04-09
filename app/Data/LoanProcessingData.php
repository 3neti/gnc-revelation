<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class LoanProcessingData extends Data
{
    public function __construct(
        public QualificationResultData $qualification,
        public CashOutScheduleData $cash_out_schedule,
        public BalancePaymentScheduleData $balance_payment_schedule,
        public ProductMatchData $product_match,
        public RemediationStrategiesData $remediation,
    ) {}
}
