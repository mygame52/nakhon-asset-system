<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'description',
        'subject_type',
        'subject_id',
        'ip_address',
        'user_agent',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record an activity log entry.
     */
    public static function log(string $action, string $module, string $description, $subject = null, array $properties = [], ?User $user = null): self
    {
        $activeUser = $user ?? Auth::user();

        $log = new self([
            'user_id'     => $activeUser ? $activeUser->id : null,
            'action'      => $action,
            'module'      => $module,
            'description' => $description,
            'ip_address'  => Request::ip(),
            'user_agent'  => Request::userAgent(),
            'properties'  => !empty($properties) ? $properties : null,
        ]);

        if ($subject && is_object($subject) && isset($subject->id)) {
            $log->subject_type = get_class($subject);
            $log->subject_id   = $subject->id;
        }

        $log->save();

        return $log;
    }
}
