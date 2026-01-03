<?php

namespace App\Models;

use App\Enums\FieldType;
use App\Models\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionField extends Model
{
    protected $fillable = ['collection_id', 'name', 'type', 'rules', 'unique', 'required', 'indexed', 'locked'];

    protected function casts(): array
    {
        return [
            'type' => FieldType::class
        ];
    }

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    public function getIcon() {
        if ($this->name === 'id') return 'lucide.key';
        if ($this->name === 'password') return 'lucide.lock';
        return match ($this->type) {
            FieldType::Number => 'lucide.hash',
            FieldType::Email => 'lucide.mail',
            FieldType::Bool => 'lucide.toggle-right',
            FieldType::Datetime => 'lucide.calendar-clock',
            FieldType::File => 'lucide.image',
            default => 'lucide.text-cursor',
        };
    }

    public static function createAuthFrom($fields): array
    {
        $fields = [
            [
                'name' => 'id',
                'type' => FieldType::Text,
                'unique' => true,
                'required' => true,
                'locked' => true
            ],
            [
                'name' => 'email',
                'type' => FieldType::Email,
                'unique' => true,
                'required' => true,
                'locked' => true
            ],
            [
                'name' => 'verified',
                'type' => FieldType::Bool,
                'unique' => false,
                'required' => false,
                'locked' => true
            ],
            [
                'name' => 'password',
                'type' => FieldType::Text,
                'unique' => false,
                'required' => true,
                'locked' => true
            ],

            ...$fields,

            [
                'name' => 'created',
                'type' => FieldType::Datetime,
                'unique' => false,
                'required' => false,
                'locked' => true
            ],
            [
                'name' => 'updated',
                'type' => FieldType::Datetime,
                'unique' => false,
                'required' => false,
                'locked' => true
            ],
        ];

        return $fields;
    }
}
