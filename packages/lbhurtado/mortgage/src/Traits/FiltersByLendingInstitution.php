<?php

namespace LBHurtado\Mortgage\Traits;

use Illuminate\Database\Eloquent\Builder;

trait FiltersByLendingInstitution
{
    /**
     * Boot the model and apply the global scope for lending institution.
     */
    protected static function bootFiltersByLendingInstitution(): void
    {
        static::addGlobalScope('lending_institution', function (Builder $query) {
            $lendingInstitution = session('lending_institution');
            if ($lendingInstitution) {
                $query->forLendingInstitution($lendingInstitution);
            }
        });
    }

    /**
     * Define a query scope to filter explicitly by lending institution.
     *
     * @param Builder $query
     * @param string|array|null $lendingInstitution
     * @return Builder
     */
    public function scopeForLendingInstitution(Builder $query, string|array|null $lendingInstitution): Builder
    {
        if (!$lendingInstitution) {
            // If no lending institution is provided, skip filtering
            return $query;
        }

        return $query->whereHas('properties', function (Builder $propertyQuery) use ($lendingInstitution) {
            $propertyQuery->forLendingInstitution($lendingInstitution); // Use Property's scope
        });
    }
}
