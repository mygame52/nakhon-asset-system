<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'type', 'code_prefix', 'description'];

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }
}
