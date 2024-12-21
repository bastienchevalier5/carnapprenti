<?php

use App\Http\Controllers\AccueilController;
use App\Http\Controllers\CompteRenduController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\LivretController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
Route::post('/verify-password', [Controller::class, 'verifyPassword']);

Route::middleware('auth:sanctum')->get('/users', function (Request $request) {
    return \App\Models\User::all();
});

Route::middleware('auth')->group(function () {
    Route::get('/', [AccueilController::class,'index'])->name('accueil');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('livret', LivretController::class);
    Route::get('observations/{livret}',[LivretController::class,'observations_form'])->name('livret.observations_form');
    Route::put('observations/{livret}',[LivretController::class,'observations'])->name('livret.observations');
    Route::get('/compte-rendu/{id_livret}/{periode?}', [CompteRenduController::class, 'show'])->name('compte_rendu.show');
    Route::post('/compte_rendus/store/{id_livret}/{periode}', [CompteRenduController::class, 'store'])->name('compte_rendus.store');
    Route::put('/compte_rendus/{compteRendu}', [CompteRenduController::class, 'update'])->name('compte_rendus.update');
    Route::get('/livrets/{livret}/pdf', [LivretController::class, 'generatePdf'])->name('livrets.pdf');

});

require __DIR__.'/auth.php';
