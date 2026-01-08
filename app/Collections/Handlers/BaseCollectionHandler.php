<?php

namespace App\Collections\Handlers;

use App\Models\Record;
use Illuminate\Support\Collection;

class BaseCollectionHandler implements CollectionTypeHandler
{
    public function beforeSave(Record $record): void
    {
        $fields = $record->collection->fields->keyBy('name');
        $data = $record->data;

        if (! $record->exists && $fields->has('created')) {
            if (! $data->has('created') || ! filled($data->get('created'))) {
                $data->put('created', now()->toIso8601String());
            }
        }

        if ($fields->has('updated')) {
            $data->put('updated', now()->toIso8601String());
        }

        // preserve created on update
        if ($record->exists && $fields->has('created')) {
            $originalData = $record->getOriginal('data');

            $originalData = $originalData instanceof Collection
                ? $originalData->toArray()
                : $originalData;

            if (isset($originalData['created'])) {
                $data->put('created', $originalData['created']);
            }
        }

        $record->data = $data;
    }
}
