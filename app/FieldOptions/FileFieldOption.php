<?php

namespace App\FieldOptions;

use App\Contracts\CollectionFieldOption;

class FileFieldOption implements CollectionFieldOption
{
    public function __construct(
        public ?array $allowedMimeTypes = null, // e.g., ['image/jpeg', 'image/png']
        public ?int $maxSize = null, // in bytes
        public ?int $minSize = null, // in bytes
        public bool $multiple = false,
        public ?int $maxFiles = null, // if multiple is true
        public bool $generateThumbnail = false,
        public ?array $thumbnailSizes = null, // e.g., ['small' => [150, 150], 'medium' => [300, 300]]
    ) {}

    public function toArray(): array
    {
        return [
            'allowedMimeTypes' => $this->allowedMimeTypes,
            'maxSize' => $this->maxSize,
            'minSize' => $this->minSize,
            'multiple' => $this->multiple,
            'maxFiles' => $this->maxFiles,
            'generateThumbnail' => $this->generateThumbnail,
            'thumbnailSizes' => $this->thumbnailSizes,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            allowedMimeTypes: $data['allowedMimeTypes'] ?? null,
            maxSize: $data['maxSize'] ?? null,
            minSize: $data['minSize'] ?? null,
            multiple: $data['multiple'] ?? false,
            maxFiles: $data['maxFiles'] ?? null,
            generateThumbnail: $data['generateThumbnail'] ?? false,
            thumbnailSizes: $data['thumbnailSizes'] ?? null,
        );
    }

    public function validate(): bool
    {
        if ($this->minSize !== null && $this->maxSize !== null) {
            if ($this->minSize > $this->maxSize) {
                return false;
            }
        }

        if (!$this->multiple && $this->maxFiles !== null) {
            return false;
        }

        if ($this->multiple && $this->maxFiles !== null && $this->maxFiles < 1) {
            return false;
        }

        if ($this->generateThumbnail && $this->thumbnailSizes === null) {
            // Set default thumbnail sizes if not provided
            $this->thumbnailSizes = ['small' => [150, 150]];
        }

        return true;
    }
}
