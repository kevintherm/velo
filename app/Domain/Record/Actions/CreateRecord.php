<?php

namespace App\Domain\Record\Actions;

use App\Delivery\Entity\SafeCollection;
use App\Delivery\Http\Requests\RecordRequest;
use App\Domain\Collection\Models\Collection;
use App\Domain\Record\Models\Record;

class CreateRecord
{
    public function execute(RecordRequest $request, Collection $collection): Record
    {
        $data = new SafeCollection($request->validated());
        $processedFileFields = app(ProcessRecordUploadedFiles::class)->execute($collection, $data);
        $data = $data->merge($processedFileFields);
        return $collection->recordRelation()->create(['data' => $data->toArray()]);
    }
}
