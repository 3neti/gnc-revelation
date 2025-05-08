<?php

namespace LBHurtado\Mortgage\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LBHurtado\Mortgage\Traits\HasMeta;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;
    use HasMeta;

}
