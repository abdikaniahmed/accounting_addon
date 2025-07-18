<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = ['name', 'type', 'code', 'is_active'];
    protected $table = 'acc_accounts';

    public $timestamps = true;

    public function journalItems()
    {
        return $this->hasMany(JournalItem::class, 'account_id');
    }

}
