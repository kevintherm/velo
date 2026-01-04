<?php

namespace App\Collections\Handlers;

use App\Models\Record;

interface CollectionTypeHandler
{
    public function beforeSave(Record $record): void;
}
