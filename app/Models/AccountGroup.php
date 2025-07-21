<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountGroup extends Model
{
    use SoftDeletes;

    protected $table = 'acc_account_groups';

    protected $fillable = [
        'name',
    ];

    public function accounts()
    {
        return $this->hasMany(Account::class, 'account_group_id');
    }
}
