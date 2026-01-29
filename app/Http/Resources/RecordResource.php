<?php

namespace App\Http\Resources;

use App\Enums\FieldType;
use Illuminate\Http\Request;
use App\FieldOptions\FileFieldOption;
use App\FieldOptions\RelationFieldOption;
use Illuminate\Http\Resources\Json\JsonResource;

class RecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $arr = [
            'collection_id' => $this->collection_id,
        ];

        foreach ($this->collection->fields as $field) {
            if ($field->hidden) {
                continue;
            }

            $value = $this->data[$field->name] ?? null;

            if ($field->type === FieldType::Relation && $field->options instanceof RelationFieldOption) {
                $options = $field->options;
                $isMultiple = $options->multiple || ($options->maxSelect && $options->maxSelect > 1);

                if (! $isMultiple && is_array($value)) {
                    $value = $value[0] ?? null;
                }
            } elseif ($field->type === FieldType::File && $field->options instanceof FileFieldOption) {
                $options = $field->options;
                $isMultiple = $options->multiple || ($options->maxFiles && $options->maxFiles > 1);

                if (! $isMultiple && is_array($value)) {
                    $value = $value[0] ?? null;
                }
            }

            $arr[$field->name] = $value;

            if ($request->has('expand')) {
                $arr['expand'] = $this->data['expand'] ?? null;
            }
        }

        return $arr;
    }
}
