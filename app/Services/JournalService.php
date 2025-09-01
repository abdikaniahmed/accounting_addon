<?php

namespace App\Services;

use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\JournalItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Throwable;

class JournalService
{
    public function create(array $header, array $lines): JournalEntry
    {
        $lines = $this->normalizeLines($lines);
        $this->assertBalanced($lines);

        $entry = DB::transaction(function () use ($header, $lines) {
            $entry = new JournalEntry();
            $entry->date           = $header['date'];
            $entry->type           = $header['type'] ?? 'general';
            $entry->journal_number = $header['journal_number'] ?? JournalEntry::nextNumber();
            $entry->reference      = $header['reference'] ?? null;
            $entry->description    = $header['description'] ?? null;

            // ⬅️ scope: stamp seller_id if current user is seller
            if ($u = Sentinel::getUser()) {
                if ($u->user_type === 'seller') {
                    $entry->seller_id = (int) $u->id;
                }
            }

            $entry->save();

            foreach ($lines as $l) {
                JournalItem::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $l['account_id'],
                    'type'             => ($l['debit'] ?? 0) > 0 ? 'debit' : 'credit',
                    'amount'           => ($l['debit'] ?? 0) > 0 ? $l['debit'] : $l['credit'],
                    'description'      => $l['memo'] ?? null,
                    'seller_id'        => $entry->seller_id, // ⬅️ mirror
                ]);
            }

            return $entry->fresh('journalItems.account');
        });

        $this->bustCaches();

        return $entry;
    }

    public function replace(JournalEntry $entry, array $header, array $lines): JournalEntry
    {
        $lines = $this->normalizeLines($lines);
        $this->assertBalanced($lines);

        $entry = DB::transaction(function () use ($entry, $header, $lines) {
            $entry->update([
                'date'        => $header['date'] ?? $entry->date,
                'type'        => $header['type'] ?? $entry->type,
                'reference'   => $header['reference'] ?? $entry->reference,
                'description' => $header['description'] ?? $entry->description,
            ]);

            $entry->journalItems()->delete();

            foreach ($lines as $l) {
                JournalItem::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $l['account_id'],
                    'type'             => ($l['debit'] ?? 0) > 0 ? 'debit' : 'credit',
                    'amount'           => ($l['debit'] ?? 0) > 0 ? $l['debit'] : $l['credit'],
                    'description'      => $l['memo'] ?? null,
                    'seller_id'        => $entry->seller_id, // keep scope
                ]);
            }

            return $entry->fresh('journalItems.account');
        });

        $this->bustCaches();

        return $entry;
    }

    public function delete(JournalEntry $entry): void
    {
        DB::transaction(function () use ($entry) {
            $entry->journalItems()->delete();
            $entry->delete();
        });

        $this->bustCaches();
    }

    private function normalizeLines(array $lines): array
    {
        $out = [];

        foreach ($lines as $i => $l) {
            $debit  = isset($l['debit'])  ? (float) $l['debit']  : 0.0;
            $credit = isset($l['credit']) ? (float) $l['credit'] : 0.0;

            if ($debit == 0.0 && $credit == 0.0) continue;
            if ($debit < 0 || $credit < 0) {
                throw ValidationException::withMessages(["lines.$i" => ['Debit/Credit cannot be negative.']]);
            }
            if (($debit > 0 && $credit > 0) || ($debit == 0 && $credit == 0)) {
                throw ValidationException::withMessages(["lines.$i" => ['Each line must have exactly one side (debit or credit).']]);
            }
            if (empty($l['account_id'])) {
                throw ValidationException::withMessages(["lines.$i.account_id" => ['Account is required.']]);
            }

            $out[] = [
                'account_id' => (int) $l['account_id'],
                'debit'      => $debit,
                'credit'     => $credit,
                'memo'       => $l['memo'] ?? $l['description'] ?? null,
            ];
        }

        if (count($out) === 0) {
            throw ValidationException::withMessages(['lines' => ['At least one line is required.']]);
        }

        return $out;
    }

    private function assertBalanced(array $lines): void
    {
        $debit = $credit = 0.0;
        foreach ($lines as $l) {
            $debit  += (float)($l['debit']  ?? 0);
            $credit += (float)($l['credit'] ?? 0);
        }
        if (round($debit, 2) !== round($credit, 2)) {
            throw ValidationException::withMessages(['lines' => ['Debits and credits must be equal.']]);
        }
    }

    private function bustCaches(): void
    {
        $sellerKey = optional(Sentinel::getUser())->id;
        Cache::forget('accounting.journal_entries.global');
        Cache::forget('accounting.journal_entries.seller.'.$sellerKey);
    }
}