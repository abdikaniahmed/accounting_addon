<?php
// app/Repositories/Admin/Accounting/AccountGroupRepository.php

namespace App\Repositories\Admin\Accounting;

use App\Models\Accounting\AccountGroup;
use App\Repositories\Interfaces\Admin\Accounting\AccountGroupInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AccountGroupRepository implements AccountGroupInterface
{
    /** Base visibility query */
    protected function baseQuery(?int $sellerId)
    {
        $q = AccountGroup::query()->orderBy('name');

        if (is_null($sellerId)) {
            // Admin/global space by default
            return $q->whereNull('seller_id');
        }

        // Seller sees global + own
        return $q->where(function ($w) use ($sellerId) {
            $w->whereNull('seller_id')->orWhere('seller_id', $sellerId);
        });
    }

    public function list(?int $sellerId = null): Collection
    {
        return $this->baseQuery($sellerId)->get();
    }

    public function paginate(array $filters = [], int $perPage = 15, ?int $sellerId = null): LengthAwarePaginator
    {
        $q = $this->baseQuery($sellerId);

        if (!empty($filters['q'])) {
            $q->where('name', 'like', '%'.$filters['q'].'%');
        }

        return $q->paginate($perPage);
    }

    public function get(int $id, ?int $sellerId = null): ?AccountGroup
    {
        $q = AccountGroup::query()->where('id', $id);

        if (is_null($sellerId)) {
            // Admin fetch: only global by default
            $q->whereNull('seller_id');
        } else {
            // Seller fetch: only own (not global) for edit/delete
            $q->where('seller_id', $sellerId);
        }

        return $q->first();
    }

    public function store(array $data, ?int $sellerId = null): bool
    {
        $payload = ['name' => $data['name']];

        // Admin creates global group (NULL); Seller creates owned group
        if (!is_null($sellerId)) {
            $payload['seller_id'] = $sellerId;
        }

        return (bool) AccountGroup::create($payload);
    }

    public function update(int $id, array $data, ?int $sellerId = null): bool
    {
        $group = $this->get($id, $sellerId);
        if (!$group) return false;

        return $group->update(['name' => $data['name']]);
    }

    public function delete(int $id, ?int $sellerId = null): bool
    {
        $group = $this->get($id, $sellerId);
        if (!$group) return false;

        return (bool) $group->delete();
    }

    public function import(array $names, ?int $sellerId = null): array
    {
        $imported = 0; $skipped = 0;

        DB::transaction(function () use ($names, $sellerId, &$imported, &$skipped) {
            foreach ($names as $name) {
                $name = trim((string) $name);
                if ($name === '') { $skipped++; continue; }

                if ($this->existsByName($name, $sellerId)) { $skipped++; continue; }

                $payload = ['name' => $name];
                if (!is_null($sellerId)) $payload['seller_id'] = $sellerId;

                AccountGroup::create($payload);
                $imported++;
            }
        });

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    public function existsByName(string $name, ?int $sellerId = null): bool
    {
        $q = AccountGroup::query()->where('name', $name);

        if (is_null($sellerId)) {
            // Admin/global space
            $q->whereNull('seller_id');
        } else {
            // Prevent duplicates within seller's own space
            $q->where('seller_id', $sellerId);
        }

        return $q->exists();
    }
}