<?php

namespace App\Models\Accounting;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = ['date', 'description'];
    protected $table = 'acc_journal_entries';

    // public function items()
    // {
    //     return $this->hasMany(JournalItem::class);
    // }

        // Add this for proper timestamp usage (optional)
    public $timestamps = true;

    // Relationships
    public function items()
    {
        return $this->hasMany(JournalItem::class, 'journal_entry_id');
    }

}
