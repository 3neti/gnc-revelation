<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use LBHurtado\Mortgage\Enums\Property\DevelopmentForm;
use LBHurtado\Mortgage\Enums\Property\DevelopmentType;
use LBHurtado\Mortgage\Models\Property;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $start = 750_000;
        $end = 4_000_000;
        $step = 500_000;
        $serial = 1;

        $statuses = ['available', 'unavailable'];
        $developmentTypes = array_map(fn($enum) => $enum->value, DevelopmentType::cases());
        $developmentForms = array_map(fn($enum) => $enum->value, DevelopmentForm::cases());
        $bufferMargins = [5, 10, 15];
        $disposableIncomeRequirements = [30, 32, 35];

        for ($tcp = $start; $tcp <= $end; $tcp += $step, $serial++) {
            $code = 'PROP' . str_pad($serial, 4, '0', STR_PAD_LEFT);

            Property::create([
                'code' => $code,
                'name' => "Property ₱" . number_format($tcp),
                'type' => 'residential',
                'cluster' => 'A' . ceil($serial / 2),
                'status' => $statuses[$serial % count($statuses)], // Deterministic alternating status
                Property::TOTAL_CONTRACT_PRICE => $tcp,
                Property::APPRAISAL_VALUE => $tcp, // same as TCP
                Property::PERCENT_LOANABLE_VALUE => 1.0, // 100%
                Property::PERCENT_MISCELLANEOUS_FEES => 8.5,
                Property::PROCESSING_FEE => 10_000,
                Property::DEVELOPMENT_TYPE => $developmentTypes[$serial % count($developmentTypes)],
                Property::DEVELOPMENT_FORM => $developmentForms[$serial % count($developmentForms)],
                Property::REQUIRED_BUFFER_MARGIN => $bufferMargins[$serial % count($bufferMargins)],
                Property::PERCENT_DISPOSABLE_INCOME_REQUIREMENT => $disposableIncomeRequirements[$serial % count($disposableIncomeRequirements)],
            ]);
        }
    }
}
