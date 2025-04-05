<?php

namespace App\Enums\Property;

enum HousingType: string
{
    case CONDOMINIUM = 'condominium';
    case DUPLEX = 'duplex';
    case ROW_HOUSE = 'row_house';
    case SINGLE_ATTACHED = 'single_attached';
    case SINGLE_DETACHED = 'single_detached';
    case QUADRUPLEX = 'quadruplex';
    case TOWNHOUSE = 'townhouse';
    case TWIN_HOMES = 'twin_homes';

    public function getName(): string
    {
        return match ($this) {
            self::CONDOMINIUM => 'Condominium',
            self::DUPLEX => 'Duplex',
            self::ROW_HOUSE => 'Row House',
            self::SINGLE_ATTACHED => 'Single Attached',
            self::SINGLE_DETACHED => 'Single Detached',
            self::QUADRUPLEX => 'Quadruplex',
            self::TOWNHOUSE => 'Townhouse',
            self::TWIN_HOMES => 'Twin Homes',
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
