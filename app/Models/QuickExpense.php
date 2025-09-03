<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;

class QuickExpense extends Model implements AuditableContract
{
    use SoftDeletes, Auditable;

    protected $table = 'acc_quick_expenses';

    protected $fillable = [
        // scoping
        'seller_id',

        // main fields
        'title',
        'description',
        'account_id',
        'payment_account_id',
        'amount',
        'bill_file',
        'date',
        'reference',
        'vendor',
        'journal_entry_id',
    ];

    protected $casts = [
        'date'      => 'date',
        'amount'    => 'decimal:2',
        'seller_id' => 'integer',
    ];

    /** Exclude noisy columns from the audit payload */
    protected $auditExclude = ['updated_at', 'deleted_at'];

    /* -------------------------
     | Relationships
     |--------------------------*/
    public function expenseAccount()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function paymentAccount()
    {
        return $this->belongsTo(Account::class, 'payment_account_id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(\App\Models\Accounting\JournalEntry::class, 'journal_entry_id');
    }

    /* -------------------------
     | Audit tags (optional)
     |--------------------------*/
    public function getAuditTags(): array
    {
        $guard = request()?->attributes?->get('_audit_guard');
        return array_filter([
            $guard ? 'guard:' . $guard : 'guard:unknown',
            class_basename($this),
        ]);
    }

    /* -------------------------
     | Scopes
     |--------------------------*/

    /** Full-text style search on common columns */
    public function scopeSearch($q, ?string $term)
    {
        if (!$term) return $q;
        return $q->where(function ($w) use ($term) {
            $w->orWhere('description', 'like', "%{$term}%")
              ->orWhere('reference', 'like', "%{$term}%")
              ->orWhere('vendor', 'like', "%{$term}%");
        });
    }

    /** Filter by date range (any side optional) */
    public function scopeBetweenDates($q, ?string $start, ?string $end)
    {
        if ($start && $end) return $q->whereBetween('date', [$start, $end]);
        if ($start)         return $q->where('date', '>=', $start);
        if ($end)           return $q->where('date', '<=', $end);
        return $q;
    }

    /** Strict: only current seller’s rows */
    public function scopeOnlyOwn($q)
    {
        if ($u = Sentinel::getUser()) {
            return $q->where('seller_id', (int) $u->id);
        }
        // no user → nothing
        return $q->whereRaw('1=0');
    }

    /** Show rows for this seller; if you support global rows (NULL), include both */
    public function scopeVisibleForCurrentSeller($q)
    {
        $u = Sentinel::getUser();
        if (!$u) {
            return $q->whereNull('seller_id');
        }
        return $q->where(function ($w) use ($u) {
            $w->where('seller_id', (int) $u->id)
              ->orWhereNull('seller_id'); // keep or remove depending on your policy
        });
    }

    /** Admin/global only (seller_id IS NULL) */
    public function scopeOnlyGlobal($q)
    {
        return $q->whereNull('seller_id');
    }

    /** Direct filter by seller id */
    public function scopeForSeller($q, ?int $sellerId)
    {
        return $sellerId ? $q->where('seller_id', $sellerId) : $q->whereRaw('1=0');
    }

    /* -------------------------
     | Auto-stamp seller_id
     |--------------------------*/
    protected static function booted()
    {
        static::creating(function (self $m) {
            // If creator is a seller and row missing seller_id, stamp it
            if (is_null($m->seller_id) && ($u = Sentinel::getUser())) {
                if (method_exists($u, 'getUserId')) {
                    // Some Sentinel setups use getUserId()
                    $m->seller_id = (int) ($u->getUserId());
                } else {
                    $m->seller_id = (int) ($u->id);
                }
            }
        });
    }
}