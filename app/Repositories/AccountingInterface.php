<?php

namespace App\Repositories\Interfaces\Admin\Addon;

interface AccountingInterface
{
    public function getAllAccounts();
    public function createAccount(array $data);
    public function getAllJournalEntries();
    public function createJournalEntry(array $data);
}
