<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $table = 'acc_assets';

    protected $fillable = [
        'asset_name','asset_code','asset_account_id','purchase_date','cost',
        'payment_account_id','journal_entry_id',
        'depreciation_method','useful_life_months','salvage_value','is_active',
    ];

    public function assetAccount()
    {
        return $this->belongsTo(Account::class, 'asset_account_id');
    }

    public function paymentAccount()
    {
        return $this->belongsTo(Account::class, 'payment_account_id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id'); // if you have a model
    }
}