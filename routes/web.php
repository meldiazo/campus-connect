<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\ServiceRequestController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/requests', [ServiceRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/create', [ServiceRequestController::class, 'create'])->middleware('role:estudiante')->name('requests.create');
    Route::post('/requests', [ServiceRequestController::class, 'store'])->middleware('role:estudiante')->name('requests.store');
    Route::get('/requests/{serviceRequest}', [ServiceRequestController::class, 'show'])->name('requests.show');
    Route::get('/requests/{serviceRequest}/edit', [ServiceRequestController::class, 'edit'])->middleware('role:estudiante')->name('requests.edit');
    Route::put('/requests/{serviceRequest}', [ServiceRequestController::class, 'update'])->middleware('role:estudiante')->name('requests.update');
    Route::post('/requests/{serviceRequest}/cancel', [ServiceRequestController::class, 'cancel'])->middleware('role:estudiante')->name('requests.cancel');
    Route::post('/requests/{serviceRequest}/attachments', [ServiceRequestController::class, 'attach'])->name('requests.attachments');
    Route::get('/attachments/{attachment}/download', [AttachmentController::class, 'download'])->name('attachments.download');
    Route::middleware('role:administrativo')->group(function () {
        Route::put('/admin/requests/{serviceRequest}', [ServiceRequestController::class, 'adminUpdate'])->name('admin.requests.update');
        Route::post('/admin/requests/{serviceRequest}/comments', [ServiceRequestController::class, 'comment'])->name('admin.requests.comments');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::resource('/resources', ResourceController::class)->except(['show']);
    });
});
