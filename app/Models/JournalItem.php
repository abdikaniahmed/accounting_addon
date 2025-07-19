<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class JournalItem extends Model
{
    protected $table = 'acc_journal_items';

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'type',
        'amount',
        'description'
    ];

    public $timestamps = true;

    public function entry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
