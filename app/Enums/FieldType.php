<?php

namespace App\Enums;

enum FieldType: string
{
    case Text = 'text';
    case Email = 'email';
    case Number = 'number';
    case Bool = 'boolean';
    case Datetime = 'datetime';
    case File = 'file';
    
    public static function toArray(): array
    {
        return array_map(
            fn ($case) => [
                'id'   => $case->value,
                'name' => $case->name,
            ],
            self::cases()
        );
    }
}
