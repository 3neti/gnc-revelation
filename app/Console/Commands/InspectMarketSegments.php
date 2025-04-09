<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Enums\Property\{DevelopmentType, DevelopmentForm, MarketSegment};

class InspectMarketSegments extends Command
{
    protected $signature = 'market:inspect {--style=friendly : Display style (friendly|raw)}';

    protected $description = 'Inspect market segment ceilings grouped by development type and form';

    public function handle(): int
    {
        $style = $this->option('style');

        $this->info("\n📊 Market Segment Thresholds (Style: {$style})\n");

        foreach (DevelopmentType::cases() as $type) {
            foreach (DevelopmentForm::cases() as $form) {
                $configPath = "gnc-revelation.property.market.ceiling.{$type->value}.{$form->value}";
                $segmentCeilings = config($configPath);

                if (!is_array($segmentCeilings)) {
                    $this->warn("⚠️  Missing config for {$type->name} / {$form->name}");
                    continue;
                }

                $this->line("🔹 Development Type: <fg=cyan>{$type->name}</>");
                $this->line("🔸 Form: <fg=yellow>{$form->name}</>");

                if ($style === 'raw') {
                    $rows = collect($segmentCeilings)
                        ->map(fn ($value, $segment) => [ucfirst($segment), number_format($value, 2)])
                        ->toArray();

                    $this->table(['Segment', 'Ceiling (PHP)'], $rows);
                } else {
                    $rows = [];
                    foreach (MarketSegment::cases() as $segment) {
                        $rows[] = [
                            'Segment' => $segment->getName(),
                            'Key'     => $segment->value,
                            'Ceiling' => number_format($segmentCeilings[$segment->value] ?? 0, 2),
                        ];
                    }

                    $this->table(["Segment", "Key", "Ceiling (₱)"], $rows, 'box');
                }

                $this->newLine();
            }
        }

        return self::SUCCESS;
    }
}
