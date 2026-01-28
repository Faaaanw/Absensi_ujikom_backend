<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return view('welcome');
});
Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
