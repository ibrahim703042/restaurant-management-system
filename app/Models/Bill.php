<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Bill extends Model
{
    protected $fillable = ['bill_number', 'order_id', 'qr_token', 'total', 'payment_status'];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    public function verifyUrl(): string
    {
        return route('bills.verify', ['token' => $this->qr_token], true);
    }

    public function totalPaid(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function remainingAmount(): float
    {
        return max(0, round((float) $this->total - $this->totalPaid(), 2));
    }

    /**
     * Align debt rows and client.debt_balance with payments (POS + QR verify).
     */
    public function syncDebtsFromPayments(): void
    {
        $clientId = $this->order?->client_id;
        $totalPaid = round((float) $this->payments()->sum('amount'), 2);
        $total = round((float) $this->total, 2);
        $remaining = max(0, round($total - $totalPaid, 2));

        DB::transaction(function () use ($clientId, $totalPaid, $total, $remaining) {
            if ($remaining < 0.01) {
                $this->update(['payment_status' => 'paid']);
                Debt::query()->where('bill_id', $this->id)->update([
                    'status' => 'settled',
                    'balance' => 0,
                    'amount_paid' => $totalPaid,
                    'amount_owed' => $total,
                ]);
            } else {
                $this->update(['payment_status' => $totalPaid > 0 ? 'partial' : 'unpaid']);
                if ($clientId) {
                    Debt::query()->updateOrCreate(
                        ['bill_id' => $this->id],
                        [
                            'client_id' => $clientId,
                            'amount_owed' => $total,
                            'amount_paid' => $totalPaid,
                            'balance' => $remaining,
                            'status' => 'open',
                        ]
                    );
                }
            }

            if ($clientId) {
                Client::query()->whereKey($clientId)->update([
                    'debt_balance' => Debt::query()
                        ->where('client_id', $clientId)
                        ->where('status', 'open')
                        ->sum('balance'),
                ]);
            }
        });
    }
}
