<?php

use App\Http\Controllers\QrCodeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [QrCodeController::class, 'create'])->name('qr.create');
Route::post('/qr', [QrCodeController::class, 'store'])->name('qr.store');
Route::post('/qr/send-email', [QrCodeController::class, 'sendEmail'])->name('qr.send-email');
Route::get('/qr/history', [QrCodeController::class, 'history'])->name('qr.history');
