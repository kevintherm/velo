<?php

use function Livewire\Volt\{state, layout, mount, rules, title};
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

title('Forgot Password');

layout('components.layouts.guest');

state(['email' => '', 'status']);

rules([
    'email' => 'required|email',
]);

$sendResetLink = function() {
    $this->validate();

    $status = \Illuminate\Support\Facades\Password::broker()->sendResetLink(
        [
            'email' => $this->email
        ]
    );

    if ($status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT) {
        $this->status = __($status);
    } else {
        $this->addError('email', __($status));
    }
};

?>

<main class="max-w-xl w-full mx-auto p-6">
    <x-form wire:submit="sendResetLink">
        <div class="flex items-center flex-col gap-4">
            <x-app-brand class="mb-6" />
            <p class="text-xl font-semibold">Forgot Password</p>
            <p>{{ $status }}</p>
        </div>

        <x-input label="Email" wire:model="email" icon="o-envelope" />

        <x-slot:actions>
            <x-button label="Send" class="btn-primary w-full" type="submit" spinner />
        </x-slot:actions>
    </x-form>
</main>
