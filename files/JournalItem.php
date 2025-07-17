<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class JournalItem extends Model
{
    /** @var string  */
    protected $table = 'acc_journal_items';      // ← matches install.sql

    /** @var bool */
    public $timestamps = true;                   // created_at / updated_at

    /** @var array  */
    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'type',          // 'debit' or 'credit'
        'amount',
    ];

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */

    /**
     * Each item belongs to one journal entry.
     */
    public function entry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    /**
     * Each item touches one account.
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}
