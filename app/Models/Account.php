<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
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
}
