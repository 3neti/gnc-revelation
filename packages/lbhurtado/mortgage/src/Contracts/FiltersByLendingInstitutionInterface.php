<?php

namespace LBHurtado\Mortgage\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface FiltersByLendingInstitutionInterface
{
    /**
     * Define a query scope to explicitly filter by lending institution.
     *
     * @param  Builder $query
     * @param  string|array|null $lendingInstitution
     * @return Builder
     */
    public function scopeForLendingInstitution(Builder $query, string|array|null $lendingInstitution): Builder;
}
