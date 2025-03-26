<?php

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
});
