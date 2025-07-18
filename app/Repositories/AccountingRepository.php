<?php

namespace App\Repositories\Admin\Addon;

use App\Models\Accounting\Account;
use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\JournalItem;
use App\Repositories\Interfaces\Admin\Addon\AccountingInterface;

class AccountingRepository implements AccountingInterface
{
    public function getAllAccounts()
    {
        return Account::all();
    }

    public function createAccount(array $data)
    {
        return Account::create($data);
    }

    public function getAllJournalEntries()
    {
        return JournalEntry::with('items')->latest()->get();
    }

    public function createJournalEntry(array $data)
    {
        $entry = JournalEntry::create([
            'date' => $data['date'],
            'description' => $data['description'] ?? null,
        ]);

        foreach ($data['items'] as $item) {
            JournalItem::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $item['account_id'],
                'type' => $item['type'],
                'amount' => $item['amount'],
            ]);
        }

        return $entry;
    }
}
