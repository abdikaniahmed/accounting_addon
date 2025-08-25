<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

// ✅ Add these:
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
    ];

    public $timestamps = true;

    // In BaseModel or in each model you want
    public function getAuditTags(): array
    {
        $guard = request()?->attributes->get('_audit_guard');

        return array_filter([
            $guard ? 'guard:'.$guard : 'guard:unknown',
            class_basename($this),
        ]);
    }


    /**
     * Reduce noise in the audit trail (optional).
     * You can also whitelist with $auditInclude instead.
     */
    protected $auditExclude = [
        'updated_at',
        'deleted_at',
    ];

    /**
     * If you only want specific fields audited, uncomment:
     */
    // protected $auditInclude = ['name','type','code','is_money','is_active','account_group_id'];

    // 🔸 Relationships
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

    // 🔸 Scopes
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

    /** Scope for only money accounts */
    public function scopeMoney($query)
    {
        return $query->where('is_money', true);
    }

    // 🔸 Auto clear cache on update/delete
    protected static function booted()
    {
        static::saved(fn () => Cache::forget('accounting.accounts'));
        static::deleted(fn () => Cache::forget('accounting.accounts'));
    }
}