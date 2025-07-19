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

    public $timestamps = true;

    protected $casts = [
        'date' => 'date',
    ];

    // Eager load by default
    protected $with = ['items.account'];

    public function items()
    {
        return $this->hasMany(JournalItem::class, 'journal_entry_id');
    }
}
