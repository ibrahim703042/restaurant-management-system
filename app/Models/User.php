<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Application login account (Spatie roles & permissions).
 *
 * **Employees vs users**
 * - Many employees never log in (kitchen, part-time, etc.).
 * - To give an employee access: create this User (email/password), assign a role,
 *   then link {@see Employee::$userId} and set {@see Employee::$canAccessApp} = true.
 * - Users without an employee record are typical for owner / external accountant.
 *
 * **Creating a user**
 * 1. Admin → Users → Add: name, email, password, role(s).
 * 2. Optional: link an employee who has no account yet (one user per employee).
 * 3. Or edit Employee later and attach user id.
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /** Employee profile linked to this login, if any. */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    public function hasLinkedEmployee(): bool
    {
        return $this->employee()->exists();
    }

    /** Assigned branches. Empty = access all active stores. */
    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class, 'store_user');
    }

    /**
     * @return Collection<int, Store>
     */
    public function accessibleStores()
    {
        $q = Store::query()->where('status', 1)->orderByDesc('is_primary_stock_location')->orderBy('name');
        $ids = $this->stores()->pluck('id');
        if ($ids->isEmpty()) {
            return $q->get();
        }

        return $q->whereIn('id', $ids)->get();
    }
}
