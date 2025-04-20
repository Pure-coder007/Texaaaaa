<?php

use App\Livewire\Estates\EstateDetailPage;
use App\Livewire\Estates\EstateListingPage;
use App\Livewire\Purchases\PurchaseSuccessPage;
use Illuminate\Support\Facades\Route;
use Filament\Actions\Exports\Http\Controllers\DownloadExport;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/estates/{estate}', EstateDetailPage::class)->name('estates.show');

Route::get('/', EstateListingPage::class)->name('home');

Route::middleware(['auth'])->group(function () {
    // Purchase process route
    Route::get('/purchases/process', \App\Livewire\Purchases\PurchaseProcessPage::class)
        ->name('purchases.process');

    Route::get('/purchases/success/{purchaseId}', PurchaseSuccessPage::class)
        ->name('purchases.success');

});

Route::get('/filament/exports/{export}/download', DownloadExport::class)
    ->name('filament.exports.download')
    ->middleware(['web', 'auth:admin']);