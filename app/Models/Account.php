<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Account extends Model
{
    use SoftDeletes;

    protected $table = 'acc_accounts';

    protected $fillable = [
        'name',
        'type',
        'code',
        'is_money',        // ✅ Added here
        'is_active',
        'account_group_id',
    ];

    public $timestamps = true;

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

    /**
     * Scope for only money accounts (Cash, Bank, Mobile Wallets, etc.)
     */
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