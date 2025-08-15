<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bill extends Model
{
    use SoftDeletes;

    protected $table = 'acc_bills';

    protected $fillable = [
        'vendor_id',
        'bill_number',
        'bill_date',
        'due_date',
        'total_amount',
        'balance_due',
        'status',
        'journal_entry_id',
        'notes',
    ];

    protected $casts = [
        'bill_date'     => 'date',
        'due_date'      => 'date',
        'total_amount'  => 'decimal:2',
        'balance_due'   => 'decimal:2',
    ];

    // ---- Relationships ----
    public function vendor()
    {
        return $this->belongsTo(Contact::class, 'vendor_id');
    }

    public function items()
    {
        return $this->hasMany(BillItem::class, 'bill_id');
    }

    public function payments()
    {
        // acc_bill_payments table (optional but used in your blades)
        return $this->hasMany(BillPayment::class, 'bill_id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    // ---- Helpers ----
    public function getPaidAmountAttribute(): float
    {
        // sum is decimal:2, cast to float for convenience
        return (float) ($this->payments()->sum('amount') ?? 0);
    }

    public function getDueAmountAttribute(): float
    {
        return max(0.0, (float) $this->balance_due);
    }

    // ---- Scopes ----
    public function scopeUnpaid($q)         { return $q->where('status', 'unpaid'); }
    public function scopePartiallyPaid($q)  { return $q->where('status', 'partially_paid'); }
    public function scopePaid($q)           { return $q->where('status', 'paid'); }
}