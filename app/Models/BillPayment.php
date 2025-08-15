<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class BillPayment extends Model
{
    protected $table = 'acc_bill_payments';

    protected $fillable = [
        'bill_id',
        'payment_account_id',
        'payment_date',
        'amount',
        'reference',
        'journal_entry_id',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    // Relationships
    public function bill()
    {
        return $this->belongsTo(Bill::class, 'bill_id');
    }

    public function paymentAccount()
    {
        return $this->belongsTo(Account::class, 'payment_account_id');
    }
}