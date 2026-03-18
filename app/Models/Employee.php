<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $table = 'employees';

    protected $fillable = [
        'user_id',
        'can_access_app',
        'hire_date',
        'employment_status',
        'first_name',
        'last_name',
        'email',
        'gender',
        'birthday',
        'position_id',
        'phone',
        'mother_name',
        'father_name',
        'country',
        'city',
        'address',
        'image',
    ];

    protected $casts = [
        'can_access_app' => 'boolean',
        'hire_date' => 'date',
        'birthday' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function workShifts(): HasMany
    {
        return $this->hasMany(WorkShift::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(EmployeeLeave::class);
    }

    public function waiterPerformances(): HasMany
    {
        return $this->hasMany(WaiterPerformance::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(EmployeeActivity::class);
    }
}
