<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = ['name', 'type', 'code', 'is_active'];
    protected $table = 'acc_accounts';

}
