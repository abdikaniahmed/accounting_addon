<?php
// app/Models/Accounting/AccountGroup.php
namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;

// ✅ Auditing
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class AccountGroup extends Model implements AuditableContract
{
    use SoftDeletes, Auditable;

    protected $table = 'acc_account_groups';

    protected $fillable = [
        'name',
        'seller_id',
    ];

    protected $casts = [
        'seller_id' => 'integer',
    ];

    /** Reduce audit noise */
    protected $auditExclude = ['updated_at', 'deleted_at'];

    public function getAuditTags(): array
    {
        $guard = request()?->attributes->get('_audit_guard');
        return array_filter([
            $guard ? 'guard:'.$guard : 'guard:unknown',
            class_basename($this),
        ]);
    }

    // ── Relationships ────────────────────────────────────────────────────────────
    public function accounts()
    {
        return $this->hasMany(Account::class, 'account_group_id');
    }

    // ── Scopes ──────────────────────────────────────────────────────────────────
    /** Seller sees own + global (NULL) */
    public function scopeVisibleForCurrentSeller($q)
    {
        $u = Sentinel::getUser();
        if (!$u) return $q->whereNull('seller_id');
        return $q->where(function ($w) use ($u) {
            $w->whereNull('seller_id')->orWhere('seller_id', $u->id);
        });
    }

    /** Only current seller’s groups */
    public function scopeOnlyOwn($q)
    {
        if ($u = Sentinel::getUser()) {
            return $q->where('seller_id', $u->id);
        }
        // no user → return none
        return $q->whereRaw('1=0');
    }

    /** Only global (admin-created) groups */
    public function scopeOnlyGlobal($q)
    {
        return $q->whereNull('seller_id');
    }

    // ── Helpers ─────────────────────────────────────────────────────────────────
    public function isOwnedBy(?int $userId): bool
    {
        return !is_null($this->seller_id) && $this->seller_id === (int) $userId;
    }

    // ── Auto-stamp seller_id on seller-created rows ─────────────────────────────
    protected static function booted()
    {
        static::creating(function ($model) {
            if (is_null($model->seller_id) && ($u = Sentinel::getUser())) {
                if ($u->user_type === 'seller') {
                    $model->seller_id = (int) $u->id;
                }
            }
        });
    }
}