<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    use SoftDeletes;

    protected $table = 'acc_journal_entries';

    protected $fillable = [
        'journal_number',
        'date',
        'reference',
        'description',
        'type',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public $timestamps = true;

    // Eager load items and related accounts
    protected $with = ['items.account'];

    // One entry has many journal items
    public function items()
    {
        return $this->hasMany(JournalItem::class, 'journal_entry_id');
    }

    // 🔹 Scope: Filter by date range
    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('date', [$start, $end]);
    }

    // 🔹 Scope: Filter by reference
    public function scopeWithReference($query, $ref)
    {
        return $query->where('reference', 'LIKE', "%{$ref}%");
    }

    // 🔹 Scope: Filter by journal number
    public function scopeWithJournalNumber($query, $number)
    {
        return $query->where('journal_number', $number);
    }
}
