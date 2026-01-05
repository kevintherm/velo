<?php

namespace App\FieldOptions;

use App\Contracts\CollectionFieldOption;

class EmailFieldOption implements CollectionFieldOption
{
    public function __construct(
        public ?array $allowedDomains = null, // e.g., ['gmail.com', 'company.com']
        public ?array $blockedDomains = null,
    ) {}

    public function toArray(): array
    {
        return [
            'allowedDomains' => $this->allowedDomains,
            'blockedDomains' => $this->blockedDomains,
        ];
    }

    public static function fromArray(array $data): static
    {
        return new static(
            allowedDomains: $data['allowedDomains'] ?? null,
            blockedDomains: $data['blockedDomains'] ?? null,
        );
    }

    public function validate(): bool
    {
        // Cannot have both allowed and blocked domains
        if ($this->allowedDomains !== null && $this->blockedDomains !== null) {
            return false;
        }

        return true;
    }
}
