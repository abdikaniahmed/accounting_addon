<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalItem extends Model
{
    use SoftDeletes;

    protected $table = 'acc_journal_items';

    protected $fillable = [
        'journal_entry_id',
        'account_id',
        'type',
        'amount',
        'description',
    ];

    public $timestamps = true;

    // Belongs to a Journal Entry
    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    // Belongs to an Account
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    // Scope: Filter by Account ID
    public function scopeByAccount($query, $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    // Scope: Filter by Type (debit/credit)
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Scope: Filter by Date Range via related JournalEntry
    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereHas('journalEntry', function ($q) use ($start, $end) {
            $q->whereBetween('date', [$start, $end]);
        });
    }
}
