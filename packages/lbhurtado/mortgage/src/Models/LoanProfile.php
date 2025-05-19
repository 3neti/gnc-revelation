<?php

namespace LBHurtado\Mortgage\Models;

use LBHurtadp\Mortgage\Database\Factories\LoanProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use FrittenKeeZ\Vouchers\Concerns\HasVouchers;
use FrittenKeeZ\Vouchers\Facades\Vouchers;
use Illuminate\Database\Eloquent\Model;

class LoanProfile extends Model
{
    use HasVouchers;
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'reference_code',
        'lending_institution',
        'total_contract_price',
        'inputs',
        'computation',
        'qualified',
        'required_equity',
        'income_gap',
        'suggested_down_payment_percent',
        'reason',
        'reserved_at'
    ];

    protected $casts = [
        'total_contract_price' => 'float',
        'inputs' => 'array',
        'computation' => 'array',
        'qualified' => 'boolean',
        'required_equity' => 'float',
        'income_gap' => 'float',
        'suggested_down_payment_percent' => 'float',
        'reason' => 'string',
        'reserved_at' => 'datetime',
    ];

    public static function newFactory(): LoanProfileFactory
    {
        return LoanProfileFactory::new();
    }

    public static function booted(): void
    {
        static::creating(function (LoanProfile $loanProfile) {
            $entities = ['loan_profile' => $loanProfile];
            $voucher = Vouchers::withEntities(...$entities)->create();
            $loanProfile->reference_code = $voucher->code;
        });
    }
}
