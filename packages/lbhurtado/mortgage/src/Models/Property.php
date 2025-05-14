<?php

namespace LBHurtado\Mortgage\Models;

use LBHurtado\Mortgage\Enums\Property\{DevelopmentForm, DevelopmentType, HousingType};
use LBHurtado\Mortgage\Traits\AdditionalPropertyAttributes;
use LBHurtadp\Mortgage\Database\Factories\PropertyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LBHurtado\Mortgage\Classes\LendingInstitution;
use LBHurtado\Mortgage\Data\Models\PropertyData;
use LBHurtado\Mortgage\ValueObjects\Percent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LBHurtado\Mortgage\Traits\HasMeta;
use Spatie\LaravelData\WithData;
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
 * @property Price  $appraisal_value
 * @property DevelopmentType $development_type
 * @property DevelopmentForm $development_form
 * @property HousingType $housing_type
 * @property Percent $percent_loanable_value
 * @property Percent $percent_miscellaneous_fees
 * @property Price $processing_fee
 * @property Percent $required_buffer_margin
 * @property LendingInstitution $lending_institution
 * @property Percent $income_requirement_multiplier
 * @property Percent $percent_down_payment
 *
 * @method int getKey()
 */
class Property extends Model
{
    use AdditionalPropertyAttributes;
    use HasFactory;
    use WithData;
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

    protected string $dataClass = PropertyData::class;

    public static function newFactory(): PropertyFactory
    {
        return PropertyFactory::new();
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'sku', 'sku', 'product');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_code', 'code', 'projects');
    }

    public function toDomain(): \LBHurtado\Mortgage\Classes\Property
    {
        return (new \LBHurtado\Mortgage\Classes\Property(
            $this->total_contract_price->inclusive()->getAmount()->toFloat(),
            $this->development_type,
            $this->development_form,
            $this->housing_type,
        ))
            ->setRequiredBufferMargin($this->required_buffer_margin)
            ->setAppraisalValue($this->appraisal_value)
            ->setProcessingFee($this->processing_fee)
            ->setPercentLoanableValue($this->percent_loanable_value)
            ->setPercentMiscellaneousFees($this->percent_miscellaneous_fees)
            ->setLendingInstitution($this->lending_institution)
            ->setIncomeRequirementMultiplier($this->income_requirement_multiplier)
            ->setPercentDownPayment($this->percent_down_payment)
            ;
    }

    /**
     * Scope to filter properties by lending institution.
     *
     * @param  Builder  $query
     * @param  array|string|null  $lendingInstitutions
     * @return Builder
     */
    public function scopeForLendingInstitution(Builder $query, array|string|null $lendingInstitutions): Builder
    {
        if (empty($lendingInstitutions)) {
            return $query; // Unfiltered if no lending institutions provided
        }

        $lendingInstitutions = is_array($lendingInstitutions) ? $lendingInstitutions : [$lendingInstitutions];

        return $query->whereIn('meta->lending_institution', $lendingInstitutions);
    }
}
