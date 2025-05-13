<?php

namespace Database\Seeders;

use LBHurtado\Mortgage\Enums\Property\DevelopmentForm;
use LBHurtado\Mortgage\Enums\Property\DevelopmentType;
use LBHurtado\Mortgage\Enums\Property\HousingType;
use LBHurtado\Mortgage\Models\Property;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $start = 800_000; // Start TCP at 800,000
        $end = 4_000_000; // End at 4,000,000
        $step = 50_000; // Increment of 50,000
        $serial = 1;

        $statuses = ['available', 'unavailable'];
        $developmentTypes = array_map(fn($enum) => $enum->value, DevelopmentType::cases());
        $developmentForms = array_map(fn($enum) => $enum->value, DevelopmentForm::cases());
        $housingTypes = array_map(fn($enum) => $enum->value, HousingType::cases());
        $bufferMargins = [5, 10, 15];
        $lendingInstitutionsAbove1M = ['rcbc', 'cbc']; // Lending institutions for TCP > 1M
        $incomeRequirementMultiplier = [30, 32, 35];

        for ($tcp = $start; $tcp <= $end; $tcp += $step, $serial++) {
            $code = 'PROP' . str_pad($serial, 4, '0', STR_PAD_LEFT);

            // Determine lending institution based on TCP
            $lendingInstitution = $tcp <= 1_000_000
                ? 'hdmf' // Use 'hdmf' if TCP is less than or equal to 1M
                : $lendingInstitutionsAbove1M[array_rand($lendingInstitutionsAbove1M)]; // Use 'rcbc' or 'cbc' randomly for TCP greater than 1M

            Property::create([
                'code' => $code,
                'name' => "Property ₱" . number_format($tcp),
                'type' => 'residential',
                'cluster' => 'A' . ceil($serial / 2),
//                'status' => $statuses[$serial % count($statuses)], // Deterministic alternating status
                'status' => 'available',
                Property::TOTAL_CONTRACT_PRICE => $tcp,
                Property::APPRAISAL_VALUE => $tcp, // same as TCP
                Property::PERCENT_LOANABLE_VALUE => 1.0, // 100%
                Property::PERCENT_MISCELLANEOUS_FEES => 8.5,
                Property::PROCESSING_FEE => 10_000,
                Property::DEVELOPMENT_TYPE => $developmentTypes[$serial % count($developmentTypes)],
                Property::DEVELOPMENT_FORM => $developmentForms[$serial % count($developmentForms)],
                Property::HOUSING_TYPE => $housingTypes[$serial % count($housingTypes)],
                Property::REQUIRED_BUFFER_MARGIN => $bufferMargins[$serial % count($bufferMargins)],
                Property::LENDING_INSTITUTION => $lendingInstitution, // Set according to TCP
                Property::INCOME_REQUIREMENT_MULTIPLIER => $incomeRequirementMultiplier[$serial % count($incomeRequirementMultiplier)],
            ]);
        }
    }
}
