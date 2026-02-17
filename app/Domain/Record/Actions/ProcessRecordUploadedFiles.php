<?php

namespace App\Domain\Record\Actions;

use App\Delivery\Entity\FileObject;
use App\Delivery\Services\HandleFileUpload;
use App\Domain\Collection\Models\Collection;
use App\Domain\Field\Enums\FieldType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection as SupportCollection;

readonly class ProcessRecordUploadedFiles
{
    public function __construct(
        private HandleFileUpload $fileHandler
    ) {
    }

    private function processFile(Collection $collection, mixed $file): ?FileObject
    {
        if (
            $file instanceof FileObject
            || (is_array($file) && isset($file['uuid']))
        ) {
            return null;
        }

        if (!$file instanceof UploadedFile) {
            return null;
        }

        return $this->fileHandler->forCollection($collection)->fromUpload($file)->save();
    }

    public function execute(Collection $collection, SupportCollection $data): SupportCollection
    {
        $processedFields = collect([]);

        $fileFields = $collection->fields->filter(fn ($field) => $field->type === FieldType::File);
        foreach ($fileFields as $field) {
            $value = $data->get($field->name);
            if (empty($value)) {
                continue;
            }

            $multiple = $field->options->multiple ?? false;
            $processedFile = null;
            $processedFiles = collect([]);

            if ($multiple) {
                $processedFiles = collect($value)->map(fn ($file) => $this->processFile($collection, $file))->filter()->values();
            } else {
                $processedFile = $this->processFile($collection, $value);
            }

            $data->put($field->name, $multiple ? $processedFiles : $processedFile);
        }

        return $processedFields;
    }
}
