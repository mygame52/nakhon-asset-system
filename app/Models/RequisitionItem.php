<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequisitionItem extends Model
{
    protected $fillable = [
        'requisition_id',
        'material_id',
        'requested_qty',
        'approved_qty',
        'status', // pending, officer_approved, approved, rejected
        'officer_approved_by',
        'officer_approved_at',
        'officer_note',
        'admin_note',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'officer_approved_at' => 'datetime',
        'approved_at' => 'datetime',
        'requested_qty' => 'integer',
        'approved_qty' => 'integer',
    ];

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(Requisition::class, 'requisition_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

    public function officerApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_approved_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
