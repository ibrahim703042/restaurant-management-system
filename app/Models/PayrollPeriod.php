<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Payroll period header (lines can be added later). Gated by hr.payroll.manage.
 */
class PayrollPeriod extends Model
{
    protected $fillable = [
        'period_start',
        'period_end',
        'status',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
    ];
}
