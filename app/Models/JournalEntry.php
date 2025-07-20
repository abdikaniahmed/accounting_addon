<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $table = 'acc_journal_entries';

    protected $fillable = [
        'journal_number',
        'date',
        'reference',
        'description',
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
}
