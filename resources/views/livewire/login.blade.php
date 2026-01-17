<?php

use function Livewire\Volt\{state, layout, mount, rules, title};
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

title('Login');

layout('components.layouts.guest');

state(['email' => 'admin@larabase.com', 'password' => 'password', 'remember' => false]);

rules([
    'email' => 'required|email',
    'password' => 'required',
]);

mount(function () {
    if (!Project::exists()) {
        \App\Helper::initProject(); // @TODO: Remove on production
        return $this->redirect(route('register'), navigate: true);
    }
});

$login = function () {
    $this->validate();

    if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
        throw ValidationException::withMessages([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    session()->regenerate();

    return $this->redirect(route('home'), navigate: true);
};

?>

<main class="max-w-xl w-full mx-auto p-6">
    <x-form wire:submit="login">
        <div class="flex justify-center">
            <x-app-brand class="mb-6" />
        </div>

        <x-input label="Email" wire:model="email" icon="o-envelope"/>
        <x-password label="Password" wire:model="password" password-icon="o-key"/>

        <div class="flex justify-between flex-wrap">
            <x-toggle label="Remember Me" wire:model="remember"/>
            <a class="link link-hover" href="{{ route('password.request') }}">Forgot password?</a>
        </div>

        <x-slot:actions>
            <x-button label="Login" class="btn-primary w-full" type="submit" spinner="login"/>
        </x-slot:actions>
    </x-form>
</main>
