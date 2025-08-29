<?php
// app/Repositories/Interfaces/Admin/Accounting/AccountGroupInterface.php

namespace App\Repositories\Interfaces\Admin\Accounting;

interface AccountGroupInterface
{
    /** List groups: 
     * - Admin: global only (seller_id = NULL) by default
     * - Seller: global + own (seller_id = $sellerId)
     */
    public function list(?int $sellerId = null);

    /** Paginate (same visibility rules as list) */
    public function paginate(array $filters = [], int $perPage = 15, ?int $sellerId = null);

    /** Get single group (must respect seller ownership if $sellerId given) */
    public function get(int $id, ?int $sellerId = null);

    /** Create group (set seller_id if provided; NULL = global) */
    public function store(array $data, ?int $sellerId = null): bool;

    /** Update group (only if owned by $sellerId, unless admin/global) */
    public function update(int $id, array $data, ?int $sellerId = null): bool;

    /** Delete group (only if owned by $sellerId; prevent deleting global from seller) */
    public function delete(int $id, ?int $sellerId = null): bool;

    /** Bulk import names; returns [imported => n, skipped => n] */
    public function import(array $names, ?int $sellerId = null): array;

    /** Check duplicate name within the current visibility (global or seller space) */
    public function existsByName(string $name, ?int $sellerId = null): bool;
}