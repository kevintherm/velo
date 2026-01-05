<?php

namespace App\Constracts;

use App\Models\Collection;

interface IndexStrategy
{
    public function createIndex(Collection $collection, string $fieldName, bool $unique = false): void;
    public function dropIndex(Collection $collection, string $fieldName): void;
    public function hasIndex(string $indexName): bool;
}
