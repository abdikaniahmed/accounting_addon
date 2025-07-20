<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;

    protected $table = 'acc_accounts';

    protected $fillable = [
        'name',
        'type',
        'code',
        'is_active',
    ];

    public $timestamps = true;

    // One account can be used in many journal items
    public function journalItems()
    {
        return $this->hasMany(JournalItem::class, 'account_id');
    }

    // 🔹 Scope: Only active accounts
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // 🔹 Scope: Filter by type (asset, liability, etc.)
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    // 🔹 Scope: Search by name or code
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('code', 'like', "%{$term}%");
        });
    }
    
    protected static function booted()
    {
        static::saved(fn () => Cache::forget('accounting.accounts'));
        static::deleted(fn () => Cache::forget('accounting.accounts'));
    }

}
