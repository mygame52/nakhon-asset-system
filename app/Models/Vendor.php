<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'tax_id', 'contact_person', 'phone', 'address'
    ];

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }
}
