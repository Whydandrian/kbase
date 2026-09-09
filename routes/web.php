<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\KnowledgeBaseController;
use App\Http\Controllers\SopController;
use Illuminate\Support\Facades\Route;

Route::get('/', [KnowledgeBaseController::class, 'index'])->name('knowledge-base');

Route::get('/domain', [KnowledgeBaseController::class, 'domains'])->name('knowledge-base.domains');
Route::get('/domain/{domain}', [KnowledgeBaseController::class, 'show'])->name('knowledge-base.domain');

Route::get('/tim', [KnowledgeBaseController::class, 'teams'])->name('knowledge-base.teams');
Route::get('/tim/{domain}', [KnowledgeBaseController::class, 'show'])->name('knowledge-base.team');

Route::get('/arsip-ta', [KnowledgeBaseController::class, 'archive'])->name('knowledge-base.archive');
Route::post('/arsip-ta', [KnowledgeBaseController::class, 'storeThesis'])
    ->middleware('throttle:10,1')
    ->name('knowledge-base.archive.store');

// Document PDF preview / download (access controlled in the controller).
Route::get('/dokumen/{document}/berkas', [DocumentController::class, 'file'])->name('documents.file');

// SOP — public browsing + PDF export + private file streaming.
Route::get('/sop', [SopController::class, 'index'])->name('sop.index');
Route::get('/sop/{sop}', [SopController::class, 'show'])->name('sop.show');
Route::get('/sop/{sop}/pdf', [SopController::class, 'pdf'])->name('sop.pdf');
Route::get('/sop/{sop}/berkas/{kind}/{id?}', [SopController::class, 'file'])->name('sop.file');

/*
|--------------------------------------------------------------------------
| Authentication (login only — no public registration)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| SOP management — any authenticated user
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/sop-baru/create', [SopController::class, 'create'])->name('sop.create');
    Route::post('/sop', [SopController::class, 'store'])->name('sop.store');
    Route::get('/sop/{sop}/edit', [SopController::class, 'edit'])->name('sop.edit');
    Route::put('/sop/{sop}', [SopController::class, 'update'])->name('sop.update');
    Route::delete('/sop/{sop}', [SopController::class, 'destroy'])->name('sop.destroy');
    Route::post('/sop/{sop}/approve', [SopController::class, 'approve'])->name('sop.approve');
    Route::post('/sop/{sop}/final', [SopController::class, 'uploadFinal'])->name('sop.final');
});

/*
|--------------------------------------------------------------------------
| Administrator — document management
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('/kategori/{category}/dokumen', [DocumentController::class, 'store'])->name('documents.store');
    Route::put('/dokumen/{document}', [DocumentController::class, 'update'])->name('documents.update');
    Route::patch('/dokumen/{document}/toggle', [DocumentController::class, 'toggle'])->name('documents.toggle');
    Route::delete('/dokumen/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
});
