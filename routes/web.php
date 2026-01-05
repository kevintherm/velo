<?php

use Livewire\Volt\Volt;
use App\Models\Collection;
use App\Livewire\CollectionPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

Route::prefix('_')->group(function () {

    Route::middleware(['throttle:60,1'])->group(function () {
        Volt::route('login', 'login')->name('login');
        Volt::route('register', 'register')->name('register');
    });

    Route::middleware(['auth', 'verified'])->group(function () {
        
        Route::get('', function (): RedirectResponse {
            $collection = Collection::firstOrFail();
            return redirect()->route('collection', ['collection' => $collection]);
        })->name('home');
        
        // Volt::route('collections/{collection:name}', 'collection')->name('collection');
        Route::get('collections/{collection:name}', CollectionPage::class)->name('collection');

        Route::get('logout', function () {
            Auth::logout();
            session()->regenerate(destroy: true);
            return redirect(route('login'));
        })->name('logout');
    });

});
