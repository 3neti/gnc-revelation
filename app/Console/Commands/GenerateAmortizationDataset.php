<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Classes\LendingInstitution;
use Illuminate\Support\Carbon;

class GenerateAmortizationDataset extends Command
{
    protected $signature = 'dataset:generate-amortization {--inst=hdmf}';

    protected $description = 'Generate a Pest dataset for amortization scenarios based on institution rules';

    public function handle(): int
    {
        $instKey = $this->option('inst');
        $institution = new LendingInstitution($instKey);

        $ages = [49, 48, 47, 46, 45];
        $tcps = [1_000_000, 1_100_000, 1_200_000, 1_300_000, 1_400_000];
        $incomes = [17_000, 19_000, 21_000, 23_000, 25_000];
        $interest = 0.0625;
        $monthlyRate = $interest / 12;

        $output = "dataset('simple amortization', [\n";

        foreach ($ages as $age) {
            $birthdate = Carbon::now()->subYears($age);
            $termYears = $institution->maxAllowedTerm($birthdate);
            $termMonths = $termYears * 12;

            foreach ($tcps as $tcp) {
                foreach ($incomes as $income) {
                    $monthly = ($tcp * $monthlyRate) / (1 - pow(1 + $monthlyRate, -$termMonths));
                    $expected = number_format(round($monthly, 2), 2);

                    $label = sprintf(
                        "    'inst: %s, age: %d, tcp: %s, mgi: %s, int: %.4f, exp_ma: %s' => [ '%s', %d, %d, %d, %.4f, %d, %s ],",
                        $instKey,
                        $age,
                        number_format($tcp, 0),
                        number_format($income, 0),
                        $interest,
                        $expected,
                        $instKey,
                        $age,
                        $tcp,
                        $income,
                        $interest,
                        $termYears,
                        $expected
                    );

                    $output .= $label . "\n";
                }
            }
        }

        $output .= "]);\n";

        $filename = base_path("tests/Datasets/" . ucfirst($instKey) . "AmortizationDataset.php");
        file_put_contents($filename, "<?php\n\n" . $output);

        $this->info("Amortization dataset generated for '{$instKey}' and saved to {$filename}");
        return Command::SUCCESS;
    }
}
