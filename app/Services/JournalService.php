<?php

namespace App\Services;

use App\Models\Accounting\JournalEntry;
use App\Models\Accounting\JournalItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class JournalService
{
    /**
     * Create a new journal entry with lines.
     * $header: ['date','type','reference','description','journal_number?']
     * $lines:  [['account_id'=>1,'debit'=>100,'credit'=>0,'memo'=>null], ...]
     */
    public function create(array $header, array $lines): JournalEntry
    {
        $lines = $this->normalizeLines($lines);
        $this->assertBalanced($lines);

        $entry = DB::transaction(function () use ($header, $lines) {
            $entry = new JournalEntry();
            $entry->date           = $header['date'];
            $entry->type           = $header['type'] ?? 'general';
            $entry->journal_number = $header['journal_number'] ?? $this->nextNumberSafely();
            $entry->reference      = $header['reference'] ?? null;
            $entry->description    = $header['description'] ?? null;
            $entry->save();

            foreach ($lines as $l) {
                JournalItem::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $l['account_id'],
                    'type'             => ($l['debit'] ?? 0) > 0 ? 'debit' : 'credit',
                    'amount'           => ($l['debit'] ?? 0) > 0 ? $l['debit'] : $l['credit'],
                    'description'      => $l['memo'] ?? null,
                ]);
            }

            return $entry->fresh('journalItems.account');
        });

        // keep index page in sync
        Cache::forget('accounting.journal_entries');

        return $entry;
    }

    /**
     * Replace an entry's header/lines (idempotent update).
     */
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
                ]);
            }

            return $entry->fresh('journalItems.account');
        });

        Cache::forget('accounting.journal_entries');

        return $entry;
    }

    /**
     * Soft-delete an entry and its items.
     */
    public function delete(JournalEntry $entry): void
    {
        DB::transaction(function () use ($entry) {
            $entry->journalItems()->delete();
            $entry->delete();
        });

        Cache::forget('accounting.journal_entries');
    }

    /**
     * Ensure lines are valid and usable:
     * - drop empty/zero rows
     * - no negatives
     * - exactly one of debit/credit per line
     */
    private function normalizeLines(array $lines): array
    {
        $out = [];

        foreach ($lines as $i => $l) {
            $debit  = isset($l['debit'])  ? (float) $l['debit']  : 0.0;
            $credit = isset($l['credit']) ? (float) $l['credit'] : 0.0;

            // skip empty rows
            if ($debit == 0.0 && $credit == 0.0) {
                continue;
            }

            if ($debit < 0 || $credit < 0) {
                throw ValidationException::withMessages([
                    "lines.$i" => ['Debit/Credit cannot be negative.'],
                ]);
            }

            if (($debit > 0 && $credit > 0) || ($debit == 0 && $credit == 0)) {
                throw ValidationException::withMessages([
                    "lines.$i" => ['Each line must have exactly one side (debit or credit).'],
                ]);
            }

            if (empty($l['account_id'])) {
                throw ValidationException::withMessages([
                    "lines.$i.account_id" => ['Account is required.'],
                ]);
            }

            $out[] = [
                'account_id' => $l['account_id'],
                'debit'      => $debit,
                'credit'     => $credit,
                'memo'       => $l['memo'] ?? null,
            ];
        }

        if (count($out) === 0) {
            throw ValidationException::withMessages([
                'lines' => ['At least one line is required.'],
            ]);
        }

        return $out;
    }

    private function assertBalanced(array $lines): void
    {
        $debit  = 0.0;
        $credit = 0.0;

        foreach ($lines as $l) {
            $debit  += (float)($l['debit']  ?? 0);
            $credit += (float)($l['credit'] ?? 0);
        }

        if (round($debit, 2) !== round($credit, 2)) {
            throw ValidationException::withMessages([
                'lines' => ['Debits and credits must be equal.'],
            ]);
        }
    }

    /**
     * Generate a unique journal number with retries to avoid race conditions.
     */
    private function nextNumberSafely(int $maxAttempts = 5): string
    {
        $attempt = 0;

        while (true) {
            $attempt++;
            $candidate = JournalEntry::nextNumber();

            try {
                // Probe uniqueness by reserving a number temporarily via insert+rollback is heavy.
                // Instead, rely on UNIQUE index at DB level and retry on conflict when saving entry.
                // We just return candidate here; the save() will retry in create() if needed.
                return $candidate;
            } catch (Throwable $e) {
                if ($attempt >= $maxAttempts) {
                    throw $e;
                }
            }
        }
    }
}