<?php

namespace LBHurtado\Mortgage\Models;

use LBHurtado\Mortgage\Traits\AdditionalPropertyAttributes;
use LBHurtadp\Mortgage\Database\Factories\PropertyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use LBHurtado\Mortgage\Traits\HasMeta;
use Whitecube\Price\Price;

/**
 * Class Property
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $type
 * @property string $cluster
 * @property string $status
 * @property string $sku
 * @property string $project_code
 * @property Price  $total_contract_price
 *
 * @method int getKey()
 */
class Property extends Model
{
    use AdditionalPropertyAttributes;
    use HasFactory;
    use HasMeta;

    protected $fillable = [
        'code',
        'name',
        'type',
        'cluster',
        'status',
        'sku',
        'project_code',
        'total_contract_price',
    ];

    public static function newFactory(): PropertyFactory
    {
        return PropertyFactory::new();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'sku', 'sku', 'product');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_code', 'code', 'projects');
    }
}
