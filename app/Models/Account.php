<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Account extends Model implements AuditableContract
{
    use SoftDeletes, Auditable;

    protected $table = 'acc_accounts';

    protected $fillable = [
        'name',
        'type',
        'code',
        'is_money',
        'is_active',
        'account_group_id',
        'seller_id',            // ✅ NEW
    ];

    protected $casts = [
        'is_money'   => 'boolean',
        'is_active'  => 'boolean',
        'seller_id'  => 'integer', // ✅ NEW
    ];

    // ── Audit tags ──────────────────────────────────────────────────────────────
    public function getAuditTags(): array
    {
        $guard = request()?->attributes->get('_audit_guard');
        return array_filter([
            $guard ? 'guard:'.$guard : 'guard:unknown',
            class_basename($this),
        ]);
    }

    protected $auditExclude = ['updated_at','deleted_at'];

    // ── Relationships (kept exactly as you asked) ───────────────────────────────
    public function journalItems()
    {
        return $this->hasMany(JournalItem::class, 'account_id');
    }

    public function accountGroup()
    {
        return $this->belongsTo(AccountGroup::class, 'account_group_id');
    }

    public function group()
    {
        return $this->accountGroup();
    }

    // ── Scopes (existing + multivendor) ─────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('code', 'like', "%{$term}%");
        });
    }

    /** Only money accounts */
    public function scopeMoney($query)
    {
        return $query->where('is_money', true);
    }

    /** ✅ Seller sees ONLY their own (no global) */
    public function scopeOnlyOwn($q)
    {
        if ($u = Sentinel::getUser()) {
            return $q->where('seller_id', (int) $u->id);
        }
        // no user → return none
        return $q->whereRaw('1=0');
    }

    /** ✅ Own + global (NULL). If you ever want that behavior for sellers. */
    public function scopeVisibleForCurrentSeller($q)
    {
        $u = Sentinel::getUser();
        if (!$u) {
            return $q->whereNull('seller_id');
        }
        return $q->where(function ($w) use ($u) {
            $w->whereNull('seller_id')->orWhere('seller_id', (int) $u->id);
        });
    }

    /** ✅ Admin/global rows (seller_id is NULL) */
    public function scopeOnlyGlobal($q)
    {
        return $q->whereNull('seller_id');
    }

    // ── Helpers ─────────────────────────────────────────────────────────────────
    public function isOwnedBy(?int $userId): bool
    {
        return !is_null($this->seller_id) && $this->seller_id === (int) $userId;
    }

    // ── Auto-stamp + cache bust ─────────────────────────────────────────────────
    protected static function booted()
    {
        // stamp seller_id when a seller creates an account
        static::creating(function ($model) {
            if (is_null($model->seller_id) && ($u = Sentinel::getUser())) {
                if ($u->user_type === 'seller') {
                    $model->seller_id = (int) $u->id;
                }
            }
        });

        static::saved(fn () => Cache::forget('accounting.accounts'));
        static::deleted(fn () => Cache::forget('accounting.accounts'));
    }
}