<?php

namespace Hzmwelec\CPQ\Models;

use Illuminate\Database\Eloquent\Model;

class CostRule extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cpq_cost_rules';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cost_id',
        'action',
        'condition',
        'description',
    ];
}
