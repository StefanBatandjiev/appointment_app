<?php

use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/app');

Route::get('{reservation}/invoice', [InvoiceController::class, 'download'])->name('reservation.invoice.download');
