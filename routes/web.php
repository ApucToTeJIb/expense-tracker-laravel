<?php

use App\Http\Controllers\ExpenseController;
use Illuminate\Support\Facades\Route;

Route::get('/expenses', [ExpenseController::class, 'index']);
Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
