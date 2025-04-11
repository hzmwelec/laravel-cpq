<?php

namespace Hzmwelec\CPQ\Models;

use Illuminate\Database\Eloquent\Model;

class Version extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cpq_versions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'uuid',
        'is_locked',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_locked' => 'bool',
        'is_active' => 'bool',
    ];

    /**
     * @return bool
     */
    public function getIsEditableAttribute()
    {
        return !$this->is_locked && !$this->is_active;
    }

    /**
     * @return bool
     */
    public function getIsDeletableAttribute()
    {
        return !$this->is_active;
    }

    /**
     * @return bool
     */
    public function getIsLockableAttribute()
    {
        return !$this->is_locked && !$this->is_active;
    }

    /**
     * @return bool
     */
    public function getIsUnlockableAttribute()
    {
        return $this->is_locked && !$this->is_active;
    }

    /**
     * @return bool
     */
    public function getIsActivableAttribute()
    {
        return $this->is_locked && !$this->is_active;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
