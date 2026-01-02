<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::prefix('_')->group(function() {

    Volt::route('', 'login_page');

});
