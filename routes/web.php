<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('{reservation}/invoice', [InvoiceController::class, 'download'])->name('reservation.invoice.download');
