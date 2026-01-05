<?php

namespace App\FieldOptions;

use App\Contracts\CollectionFieldOption;

class BoolFieldOption implements CollectionFieldOption
{
    public function __construct(
        public ?bool $defaultValue = null,
        public ?string $trueLabel = null,  // e.g., 'Yes', 'Active', 'Enabled'
        public ?string $falseLabel = null, // e.g., 'No', 'Inactive', 'Disabled'
        public string $displayAs = 'toggle', // 'toggle', 'checkbox', 'switch', 'radio'
    ) {}

    public function toArray(): array
    {
        return [
            'defaultValue' => $this->defaultValue,
            'trueLabel' => $this->trueLabel,
            'falseLabel' => $this->falseLabel,
            'displayAs' => $this->displayAs,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            defaultValue: $data['defaultValue'] ?? null,
            trueLabel: $data['trueLabel'] ?? null,
            falseLabel: $data['falseLabel'] ?? null,
            displayAs: $data['displayAs'] ?? 'toggle',
        );
    }

    public function validate(): bool
    {
        $allowedDisplayTypes = ['toggle', 'checkbox', 'switch', 'radio'];
        
        if (!\in_array($this->displayAs, $allowedDisplayTypes)) {
            return false;
        }

        return true;
    }
}
