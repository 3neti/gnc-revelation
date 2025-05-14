<?php

namespace LBHurtado\Mortgage\Models;

use LBHurtado\Mortgage\Contracts\FiltersByLendingInstitutionInterface;
use LBHurtado\Mortgage\Traits\FiltersByLendingInstitution;
use LBHurtadp\Mortgage\Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use LBHurtado\Mortgage\Factories\MoneyFactory;
use Illuminate\Database\Eloquent\Model;
use LBHurtado\Mortgage\Traits\HasMeta;
use Whitecube\Price\Price;
use Brick\Money\Money;

/**
 * Class Product
 *
 * @property int    $id
 * @property string $sku
 * @property string $name
 * @property string $brand
 * @property string $category
 * @property string $description
 * @property Price  $price
 *
 * @method int getKey()
 */
class Product extends Model implements FiltersByLendingInstitutionInterface
{
    use FiltersByLendingInstitution;
    use HasFactory;
    use HasMeta;

    protected $fillable = [
        'sku',
        'name',
        'brand',
        'category',
        'description',
        'price',
    ];

    public static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'sku', 'sku');
    }

    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => MoneyFactory::priceOfMinor($value),
            set: fn ($value) => match (true) {
                $value instanceof Price => $value->inclusive()->getMinorAmount()->toInt(),
                $value instanceof Money => $value->getMinorAmount()->toInt(),
                default => MoneyFactory::of($value)->getMinorAmount()->toInt(),
            },
        );
    }
}
