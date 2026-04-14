<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_type', 'item_type', 'item_id', 'quantity',
        'user_id', 'reference_doc', 'note', 'transaction_date', 'status'
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->item_type === 'asset' 
            ? $this->belongsTo(Asset::class, 'item_id') 
            : $this->belongsTo(Material::class, 'item_id');
    }
}
