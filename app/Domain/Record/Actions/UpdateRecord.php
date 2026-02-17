<?php

namespace App\Domain\Record\Actions;

use App\Delivery\Entity\SafeCollection;
use App\Delivery\Http\Requests\RecordRequest;
use App\Domain\Collection\Enums\CollectionType;
use App\Domain\Collection\Models\Collection;
use App\Domain\Record\Models\Record;
use Illuminate\Validation\ValidationException;

class UpdateRecord
{
    public function __construct(
        private readonly ProcessRecordUploadedFiles $fileHandler
    ) {
    }

    public function execute(RecordRequest $request, Collection $collection, string $recordId): Record
    {
        if ($collection->type === CollectionType::Auth && array_key_exists('email', $request->validated())) {
            throw ValidationException::withMessages([
                'email' => 'Use request update email endpoint for updating email',
            ]);
        }

        if ($collection->type === CollectionType::Auth && array_key_exists('password', $request->validated())) {
            throw ValidationException::withMessages([
                'password' => 'Use reset password endpoint for updating password',
            ]);
        }

        $record = $collection->records()->filter('id', '=', $recordId)->firstRawOrFail();
        $data = new SafeCollection($request->validated());
        $data = $data->merge($this->fileHandler->execute($collection, $data));
        $data = [...$record->data->toArray(), ...$data->toArray()];

        $record->data = $data;
        $record->save();

        return $record;
    }
}
