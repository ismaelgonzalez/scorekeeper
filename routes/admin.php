<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LeagueController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::resource('leagues', LeagueController::class)
    ->except('show')
    ->middlewareFor('index', 'can:view leagues')
    ->middlewareFor('create', 'can:create leagues')
    ->middlewareFor('edit', 'can:edit leagues')
    ->middlewareFor('destroy', 'can:delete leagues');
