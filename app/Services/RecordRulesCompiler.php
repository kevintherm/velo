<?php

namespace App\Services;

use App\Constracts\IndexStrategy;
use App\Enums\FieldType;
use App\FieldOptions\DatetimeFieldOption;
use App\FieldOptions\EmailFieldOption;
use App\FieldOptions\FileFieldOption;
use App\FieldOptions\NumberFieldOption;
use App\FieldOptions\TextFieldOption;
use App\Helper;
use App\Models\Collection;
use App\Models\CollectionField;
use Illuminate\Validation\Rule;

class RecordRulesCompiler
{
    public function __construct(
        protected Collection $collection,
        private IndexStrategy $indexManager
    ) {}

    /**
     * Returns a laravel style rules for each fields
     * @return string[][]
     */
    public function getRules(): array
    {
        $rules = [];

        foreach ($this->collection->fields as $field) {
            if (\in_array($field->name, ['created', 'updated'])) {
                continue;
            }

            $fieldRules = $this->compileFieldRules($field);
            $rules['form.' . $field->name] = $fieldRules;
        }

        return $rules;
    }

    /**
     * Compile validation rules for a single field
     * @param CollectionField $field
     * @return array<mixed|string|\Illuminate\Validation\Rules\In>
     */
    protected function compileFieldRules(CollectionField $field): array
    {
        $collection = $field->collection;
        $fieldRules = [];

        // Basic required/nullable rules
        if ($field->name === 'id') {
            $fieldRules[] = 'nullable';
        } elseif ($field->required) {
            $fieldRules[] = 'required';
        } else {
            $fieldRules[] = 'nullable';
        }

        // Unique via generated unique index
        if ($field->unique) {
            if ($this->indexManager->hasIndex(Helper::generateIndexName($collection, $field->name, unique: true))) {
                $fieldRules[] = Rule::unique('records', $field->name)->where(fn($q) => $q->where('collection_id', $collection->id));
            } else {
                \Log::alert('Unique index not found. Reverting to fallback.', [
                    'collection' => $collection->name,
                    'field' => $field->name
                ]);

                $fieldRules[] = Rule::unique('records', "data->{$field->name}")->where('collection_id', $collection->id);
            }
        }

        // Type-specific rules
        $fieldRules = [...$fieldRules, ...$this->getTypeRules($field)];

        // Option-specific rules
        if ($field->options) {
            $fieldRules = [...$fieldRules, ...$this->getOptionRules($field)];
        }

        return $fieldRules;
    }

    /**
     * Get basic type validation rules
     * @param CollectionField $field
     * @return string[]
     */
    protected function getTypeRules(CollectionField $field): array
    {
        return match ($field->type) {
            FieldType::Email => ['email'],
            FieldType::Number => ['numeric'],
            FieldType::Bool => ['boolean'],
            FieldType::Datetime => ['date'],
            FieldType::File => ['file'],
            FieldType::Text => ['string'],
            default => [],
        };
    }

    /**
     * Get validation rules from field options
     * @param CollectionField $field
     * @return array<string|\Illuminate\Validation\Rules\In>
     */
    protected function getOptionRules(CollectionField $field): array
    {
        $rules = [];
        $options = $field->options;

        switch (true) {
            case $options instanceof TextFieldOption:
                if ($options->minLength !== null) {
                    $rules[] = "min:{$options->minLength}";
                }
                if ($options->maxLength !== null) {
                    $rules[] = "max:{$options->maxLength}";
                }
                if ($options->pattern !== null) {
                    $rules[] = "regex:{$options->pattern}";
                }
                break;

            case $options instanceof EmailFieldOption:
                if ($options->allowedDomains !== null && !empty($options->allowedDomains)) {
                    $domains = implode(',', $options->allowedDomains);
                    $rules[] = "email:rfc,dns,filter";
                    // Custom rule for domain validation would need to be added
                }
                if ($options->blockedDomains !== null && !empty($options->blockedDomains)) {
                    // Custom rule for blocked domain validation would need to be added
                }
                break;

            case $options instanceof NumberFieldOption:
                if ($options->min !== null) {
                    $rules[] = "min:{$options->min}";
                }
                if ($options->max !== null) {
                    $rules[] = "max:{$options->max}";
                }
                if (!$options->allowDecimals) {
                    $rules[] = 'integer';
                    $rules[] = 'integer,decimal:0,2';
                }
                break;

            case $options instanceof DatetimeFieldOption:
                if ($options->minDate !== null) {
                    $rules[] = "after_or_equal:{$options->minDate}";
                }
                if ($options->maxDate !== null) {
                    $rules[] = "before_or_equal:{$options->maxDate}";
                }
                break;

            case $options instanceof FileFieldOption:
                if ($options->allowedMimeTypes !== null && !empty($options->allowedMimeTypes)) {
                    $mimes = implode(',', $options->allowedMimeTypes);
                    $rules[] = "mimetypes:{$mimes}";
                }
                if ($options->maxSize !== null) {
                    // Convert bytes to kilobytes for Laravel validation
                    $maxKb = ceil($options->maxSize / 1024);
                    $rules[] = "max:{$maxKb}";
                }
                if ($options->minSize !== null) {
                    $minKb = floor($options->minSize / 1024);
                    $rules[] = "min:{$minKb}";
                }
                break;
        }

        return $rules;
    }
}
