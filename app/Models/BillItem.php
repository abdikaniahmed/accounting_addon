<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class BillItem extends Model
{
    protected $table = 'acc_bill_items';

    protected $fillable = [
        'bill_id',
        'account_id',
        'description',
        'quantity',
        'unit_price',
        'total',
    ];

    protected $casts = [
        'quantity'   => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total'      => 'decimal:2',
    ];

    public function bill()
    {
        return $this->belongsTo(Bill::class, 'bill_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}