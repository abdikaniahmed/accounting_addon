<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuickExpense extends Model
{
    use SoftDeletes;

    protected $table = 'acc_quick_expenses';

    protected $fillable = [
        'title','description','account_id','payment_account_id','amount','bill_file',
        'date','reference','vendor','journal_entry_id',
    ];
    public $timestamps = true;

    // 🔸 Relationships
    public function expenseAccount()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function paymentAccount()
    {
        return $this->belongsTo(Account::class, 'payment_account_id');
    }

    // 🔸 Scopes
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhere('reference', 'like', "%{$term}%")
              ->orWhere('vendor', 'like', "%{$term}%");
        });
    }

    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('date', [$start, $end]);
    }

    public function journalEntry()
    {
        return $this->belongsTo(\App\Models\Accounting\JournalEntry::class, 'journal_entry_id');
    }

}