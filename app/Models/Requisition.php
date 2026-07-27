<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Requisition extends Model
{
    protected $fillable = [
        'requisition_code',
        'user_id',
        'department_id',
        'status',
        'reason_for_request',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RequisitionItem::class, 'requisition_id');
    }

    public function approver(): BelongsTo
    {
        // Get the latest approver from items
        $latestApprovedItem = $this->items()->whereNotNull('approved_by')->latest('approved_at')->first();
        return $this->belongsTo(User::class, 'approved_by')->withDefault(function () use ($latestApprovedItem) {
            return $latestApprovedItem ? $latestApprovedItem->approver : null;
        });
    }
}
