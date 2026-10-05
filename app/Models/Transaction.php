<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'transaction_type',
        'item_type',
        'item_id',
        'quantity',
        'unit_price',
        'user_id',
        'party_name',
        'reference_doc',
        'note',
        'transaction_date',
        'status',
        'deleted_by',
        'delete_reason',
        'edited_by',
        'edited_at',
        'edit_reason',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'edited_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'item_id');
    }

    public function item()
    {
        return $this->item_type === 'asset' 
            ? $this->belongsTo(Asset::class, 'item_id') 
            : $this->belongsTo(Material::class, 'item_id');
    }
}
