<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class EmployeeActivity extends Model
{
    protected $fillable = [
        'employee_id', 'user_id', 'action', 'description',
        'entity_type', 'entity_id', 'meta',
    ];

    protected $casts = ['meta' => 'array'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function logForAuthUser(?string $action, string $description, ?string $entityType = null, ?int $entityId = null, ?array $meta = null): void
    {
        try {
            if (! Schema::hasTable('employee_activities')) {
                return;
            }
            $user = auth()->user();
            if (! $user) {
                return;
            }
            $emp = $user->employee;
            if (! $emp) {
                return;
            }
            self::query()->create([
                'employee_id' => $emp->id,
                'user_id' => $user->id,
                'action' => $action ?? 'action',
                'description' => $description,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'meta' => $meta,
            ]);
        } catch (\Throwable) {
            // DB down or migration not run — do not break checkout/login flows
        }
    }
}
