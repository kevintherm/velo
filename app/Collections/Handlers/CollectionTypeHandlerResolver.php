<?php

namespace App\Collections\Handlers;

use App\Enums\CollectionType;

class CollectionTypeHandlerResolver
{
    public static function resolve(CollectionType $type): ?CollectionTypeHandler
    {
        return match ($type) {
            CollectionType::Auth => app(AuthCollectionHandler::class),
            default => null,
        };
    }
}
