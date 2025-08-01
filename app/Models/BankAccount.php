<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use SoftDeletes;

    protected $table = 'acc_bank_accounts';

    protected $fillable = [
        'account_id',
        'bank_name',
        'account_number',
        'holder_name',
        'contact_number',
        'bank_branch',
        'opening_balance',
        'address',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}