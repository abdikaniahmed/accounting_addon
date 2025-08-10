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

    // ✅ Eager load items and their related accounts
    protected $with = ['journalItems.account'];

    // ✅ Relationship: One entry has many journal items
    public function journalItems()
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

    // ✅ Smart and safe journal number generator
    public static function nextNumber()
    {
        $last = self::withTrashed()->latest('id')->first();
        $lastNumber = $last ? (int) filter_var($last->journal_number, FILTER_SANITIZE_NUMBER_INT) : 0;

        return '#JUR' . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
    }

    // app/Models/Accounting/JournalEntry.php
    public function source()
    {
        return $this->morphTo();
    }

}