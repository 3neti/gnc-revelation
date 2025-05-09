<?php

namespace LBHurtado\Mortgage\Traits;

use Spatie\SchemalessAttributes\Casts\SchemalessAttributes;
use Illuminate\Database\Eloquent\Builder;

trait HasMeta
{
    protected array $schemalessAttributes = ['meta'];

    public function initializeHasMeta(): void
    {
        $this->mergeFillable(['meta']);
        $this->mergeCasts([
            'meta' => SchemalessAttributes::class,
        ]);
        $this->setHidden(array_merge($this->getHidden(), ['meta']));
    }
//
//    public function scopeWithMeta(): Builder
//    {
//        return $this->meta->modelScope();
//    }

//    public function scopeWithMeta(Builder $query, array $keys): Builder
//    {
//        foreach ($keys as $key) {
//            $query->where("meta->{$key}", '!=', null);
//        }
//
//        return $query;
//    }


    public function scopeWithMeta(Builder $query, string|array $key, mixed $operator = null, mixed $value = null): Builder
    {
        if (is_array($key)) {
            foreach ($key as $k) {
                $query->where("meta->{$k}", '!=', null);
            }
        } elseif (func_num_args() === 2) {
            // When called like ->withMeta('some_key')
            $query->where("meta->{$key}", '!=', null);
        } elseif (func_num_args() === 4) {
            // When called like ->withMeta('key', '>=', 1000000)
            $query->where("meta->{$key}", $operator, $value);
        } else {
            throw new \InvalidArgumentException('Invalid arguments for scopeWithMeta.');
        }

        return $query;
    }
}
