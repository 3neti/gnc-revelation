<?php

namespace App\Enums\Property;

enum DevelopmentType: string
{
    case BP_957 = 'bp_957';
    case BP_220 = 'bp_220';

    public function getName(): string
    {
        return match ($this) {
            self::BP_957 => 'BP 957',
            self::BP_220 => 'BP 220',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn(self $type) => ['value' => $type->value, 'label' => $type->getName()],
            self::cases()
        );
    }
}
