<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use SoftDeletes;

    protected $table = 'acc_contacts';

    protected $fillable = [
        'type',     // 'customer' or 'vendor'
        'name',
        'email',
        'phone',
        'address',
    ];

    public $timestamps = true;

    // 🔸 Scopes
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    // 🔸 Getters (optional helpers)
    public function isCustomer()
    {
        return $this->type === 'customer';
    }

    public function isVendor()
    {
        return $this->type === 'vendor';
    }
}