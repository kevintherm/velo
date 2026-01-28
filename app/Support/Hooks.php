<?php

namespace App\Support;

class Hooks
{
    /**
     * @var array
     */
    protected array $hooks = [];

    /**
     * Register a new hook.
     *
     * @param  string  $event
     * @param  callable  $callback
     * @param  int  $priority
     * @return void
     */
    public function on(string $event, callable $callback, int $priority = 10): void
    {
        if (! isset($this->hooks[$event])) {
            $this->hooks[$event] = [];
        }

        $this->hooks[$event][] = [
            'callback' => $callback,
            'priority' => $priority,
        ];

        // Sort by priority (higher first)
        usort($this->hooks[$event], fn ($a, $b) => $b['priority'] <=> $a['priority']);
    }

    /**
     * Apply filter hooks to a value.
     *
     * @param  string  $event
     * @param  mixed  $value
     * @param  array  $context
     * @return mixed
     */
    public function apply(string $event, mixed $value, array $context = []): mixed
    {
        if (! isset($this->hooks[$event])) {
            return $value;
        }

        foreach ($this->hooks[$event] as $hook) {
            $value = call_user_func($hook['callback'], $value, $context);
        }

        return $value;
    }

    /**
     * Trigger action hooks.
     *
     * @param  string  $event
     * @param  array  $context
     * @return void
     */
    public function trigger(string $event, array $context = []): void
    {
        if (! isset($this->hooks[$event])) {
            return;
        }

        foreach ($this->hooks[$event] as $hook) {
            call_user_func($hook['callback'], $context);
        }
    }
}
