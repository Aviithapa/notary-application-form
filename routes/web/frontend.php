<?php

use Illuminate\Support\Facades\Route;



Route::get('lang/{locale}', fn ($locale) => switchLanguage($locale))->name('lang.switch');
