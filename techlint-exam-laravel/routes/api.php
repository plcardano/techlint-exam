<?php

use App\Http\Controllers\v1\AuditLog\AuditLogController;
use App\Http\Controllers\v1\Auth\AuthController;
use App\Http\Controllers\v1\IpAddress\IpAddressController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')
        ->controller(AuthController::class)
        ->group(function () {
            Route::post('login', 'login');

            Route::middleware(['auth:api'])->group(function () {
                Route::post('logout', 'logout');
                Route::post('refresh', 'refresh');
                Route::get('me', 'me');
            });
        });

    Route::prefix('ip-address')
        ->middleware(['auth:api'])
        ->controller(IpAddressController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index')->middleware('permission:can-view-ip-addresses');
            Route::post('/store', 'store')->name('store')->middleware('permission:can-create-ip-addresses');
            Route::get('{ipAddress}/show', 'show')->name('show')->middleware('permission:can-view-ip-addresses');
            Route::put('{ipAddress}/update', 'update')->name('update');
            Route::delete('{ipAddress}/delete', 'destroy')->name('destroy')->middleware('permission:can-delete-ip-addresses');
        });

    Route::prefix('audit-logs')
        ->middleware(['auth:api', 'permission:can-view-audit-logs'])
        ->controller(AuditLogController::class)
        ->group(function () {
            Route::get('/', 'index')->name('audit.index');
            Route::get('/ip-address/{ipAddressId}', 'forIpAddress')->name('audit.ip-address');
            Route::get('/user/{userId}', 'forUser')->name('audit.user');
            Route::get('/session/{sessionId}', 'forSession')->name('audit.session');
            Route::get('/current-session', 'forCurrentSession')->name('audit.current-session');
        });
});
