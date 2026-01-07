<?php

namespace App\Contracts;

use App\Models\Collection;

interface IndexStrategy
{
    /**
     * Create an index on the database used. This method also updates the relevant collection fields’ unique and required properties.
     * @param Collection $collection
     * @param array $fieldNames
     * @param bool $unique
     * @return void
     */
    public function createIndex(Collection $collection, array $fieldNames, bool $unique = false): void;
    
    /**
     * Drop an existing index on the database. This method also updates the relevant collection fields unique and required property.
     * @param Collection $collection
     * @param array $fieldNames
     * @return void
     */
    public function dropIndex(Collection $collection, array $fieldNames): void;

    /**
     * Perform a full check of both the actual generated columns and the tracking metadata in the collection_indexes table.
     * @param Collection $collection
     * @param array $fieldNames
     * @param bool $unique
     * @return void
     */
    public function hasIndex(Collection $collection, array $fieldNames, bool $unique = false): bool;
}
