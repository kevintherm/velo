<?php

namespace App\Facades;

use App\Support\Hooks as HooksInstance;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void on(string $event, callable $callback, int $priority = 10)
 * @method static mixed apply(string $event, mixed $value, array $context = [])
 * @method static void trigger(string $event, array $context = [])
 *
 * @see \App\Support\Hooks
 */
class Hooks extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return HooksInstance::class;
    }
}
