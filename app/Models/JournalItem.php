<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;

// Auditing
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class JournalItem extends Model implements AuditableContract
{
    use SoftDeletes, Auditable;

    protected $table = 'acc_journal_items';

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'type',
        'amount',
        'description',
        'seller_id',        // ⬅️ multivendor (mirror parent)
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'seller_id' => 'integer',
    ];

    public $timestamps = true;

    protected $auditExclude = ['updated_at','deleted_at'];

    public function getAuditTags(): array
    {
        $guard = request()?->attributes->get('_audit_guard');
        return array_filter([
            $guard ? 'guard:'.$guard : 'guard:unknown',
            class_basename($this),
        ]);
    }

    // Relationships
    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    // Scopes
    public function scopeByAccount($q, $accountId)
    {
        return $q->where('account_id', $accountId);
    }

    public function scopeOfType($q, $type)
    {
        return $q->where('type', $type);
    }

    public function scopeBetweenDates($q, $start, $end)
    {
        return $q->whereHas('journalEntry', function ($qq) use ($start, $end) {
            $qq->whereBetween('date', [$start, $end]);
        });
    }

    public function scopeOnlyGlobal($q)
    {
        return $q->whereNull('seller_id');
    }

    public function scopeOnlyOwn($q)
    {
        if ($u = Sentinel::getUser()) {
            return $q->where('seller_id', (int) $u->id);
        }
        return $q->whereRaw('1=0');
    }

    protected static function booted()
    {
        static::creating(function ($m) {
            // Prefer parent entry’s seller_id; otherwise fallback to current seller
            if (is_null($m->seller_id)) {
                if ($m->journal_entry_id && $m->journalEntry && !is_null($m->journalEntry->seller_id)) {
                    $m->seller_id = (int) $m->journalEntry->seller_id;
                } elseif ($u = Sentinel::getUser()) {
                    if ($u->user_type === 'seller') {
                        $m->seller_id = (int) $u->id;
                    }
                }
            }
        });
    }
}