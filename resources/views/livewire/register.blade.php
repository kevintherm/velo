<?php

use function Livewire\Volt\{state, layout, mount, rules};
use App\Models\{Project, User, Collection, CollectionField};
use App\Helper;

layout('components.layouts.guest');

state(['email', 'password', 'password_confirmation']);

rules([
    'email' => 'required|email|unique:users',
    'password' => 'required|min:6|confirmed',
]);

mount(function() {
    if (Project::exists() && User::exists()) {
        return $this->redirect(route('login'), navigate: true);
    }
});

$register = function() {
    $this->validate();

    $user = Helper::initProject($this->email, $this->password);

    Auth::login($user);

    return $this->redirect(route('home'), navigate: true);
};

?>

<main class="max-w-xl w-full mx-auto p-6">
    <x-form wire:submit="register">
        <x-app-brand class="mb-6" />

        <x-input label="Email" wire:model="email" icon="o-envelope" />
        <x-password label="Password" wire:model="password" password-icon="o-lock-closed" />
        <x-password label="Confirm Password" wire:model="password_confirmation" password-icon="o-lock-closed" />

        <x-slot:actions>
            <x-button label="Register" class="btn-primary w-full" type="submit" spinner="register" />
        </x-slot:actions>
    </x-form>
</main>
