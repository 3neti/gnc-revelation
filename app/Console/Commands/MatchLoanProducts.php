<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use LBHurtado\Mortgage\Classes\{Buyer, LendingInstitution};
use LBHurtado\Mortgage\Data\Match\LoanProductData;
use LBHurtado\Mortgage\Services\LoanMatcherService;
use Illuminate\Support\Number;

class MatchLoanProducts extends Command
{
    /**
     * The signature of the command.
     */
    protected $signature = 'loan:match';

    /**
     * The description of the command.
     */
    protected $description = 'Match a buyer with loan products they qualify for';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $age = (int) $this->ask('What is your age?');
        $income = (float) $this->ask('What is your monthly gross income?');

        $buyer = app(Buyer::class)
            ->setAge($age)
            ->setMonthlyGrossIncome($income)
            ->setIncomeRequirementMultiplier(0.35)
            ->setLendingInstitution(new LendingInstitution('hdmf'));

        $products = collect([
            new LoanProductData('P750', 'RDG750', 750_000, 0.0625, 20, 1.0, 0.35),
            new LoanProductData('P1000', 'RDG1000', 1_000_000, 0.0625, 25, 1.0, 0.35),
            new LoanProductData('P1500', 'RDG1500', 1_500_000, 0.0625, 30, 1.0, 0.35),
            new LoanProductData('P2000', 'RDG2000', 2_000_000, 0.0625, 30, 1.0, 0.35),
        ]);

        $matcher = new LoanMatcherService();
        $results = $matcher->match($buyer, $products);

        $qualified = $results->filter(fn ($r) => $r->qualified);
        $this->line("\n✅ You qualify for the following products:");
        $qualified->each(function ($r) {
            $amort = Number::currency($r->monthly_amortization->inclusive()->getAmount()->toFloat());
            $this->line("- {$r->product_code}: {$amort} / month");
        });

        if ($this->confirm('Would you like to explore additional options with remedies?')) {
            $unqualified = $results->filter(fn ($r) => !$r->qualified);
            if ($unqualified->isEmpty()) {
                $this->line("\n🎉 You already qualify for all available products.");
            } else {
                $this->line("\n⚠️  You don’t currently qualify for these, but here’s how you might:");
                $unqualified->each(function ($r) {

                    $gap = Number::currency($r->gap);
                    $suggested = Number::currency($r->suggested_equity->inclusive()->getAmount()->toFloat());
                    $amort = Number::currency($r->monthly_amortization->inclusive()->getAmount()->toFloat());

                    $this->line("- {$r->product_code}: {$amort} / month");
                    $this->line("    💡 Remedy: Increase income by {$gap} or add {$suggested} equity.");
                });
            }
        }

        return self::SUCCESS;
    }
}
