<?php

namespace Hzmwelec\CPQ\Models;

use Illuminate\Database\Eloquent\Model;

class FactorOption extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cpq_factor_options';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'factor_id',
        'name',
        'code',
        'description',
        'sort_order',
    ];
}
