<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;

// Auditing
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class JournalEntry extends Model implements AuditableContract
{
    use SoftDeletes, Auditable;

    protected $table = 'acc_journal_entries';

    protected $fillable = [
        'journal_number',
        'date',
        'reference',
        'description',
        'type',
        'seller_id',        // ⬅️ multivendor
    ];

    protected $casts = [
        'date' => 'date',
        'seller_id' => 'integer',
    ];

    public $timestamps = true;

    protected $with = ['journalItems.account'];

    // Auditing tags
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
    public function journalItems()
    {
        return $this->hasMany(JournalItem::class, 'journal_entry_id');
    }

    public function source()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeBetweenDates($q, $start, $end)
    {
        return $q->whereBetween('date', [$start, $end]);
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

    public static function nextNumber()
    {
        $last = self::withTrashed()->latest('id')->first();
        $lastNumber = $last ? (int) filter_var($last->journal_number, FILTER_SANITIZE_NUMBER_INT) : 0;
        return '#JUR' . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
    }

    protected static function booted()
    {
        static::creating(function ($m) {
            if (is_null($m->seller_id) && ($u = Sentinel::getUser()) && $u->user_type === 'seller') {
                $m->seller_id = (int) $u->id;
            }
        });
    }
}