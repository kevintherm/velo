<?php

namespace App\FieldOptions;

use App\Contracts\CollectionFieldOption;

class TextFieldOption implements CollectionFieldOption
{
    public function __construct(
        public ?int $minLength = null,
        public ?int $maxLength = null,
        public ?string $pattern = null,
    ) {}

    public function toArray(): array
    {
        return [
            'minLength' => $this->minLength,
            'maxLength' => $this->maxLength,
            'pattern' => $this->pattern,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            minLength: $data['minLength'] ?? null,
            maxLength: $data['maxLength'] ?? null,
            pattern: $data['pattern'] ?? null,
        );
    }

    public function validate(): bool
    {
        if ($this->minLength !== null && $this->maxLength !== null) {
            if ($this->minLength > $this->maxLength) {
                return false;
            }
        }

        if ($this->minLength !== null && $this->minLength < 0) {
            return false;
        }

        return true;
    }
}
