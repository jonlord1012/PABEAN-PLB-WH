<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\C_home;

Route::post('/auth', [C_home::class, 'auth']);
Route::post('/send_email', [C_home::class, 'send_email']);
Route::post('/check_token', [C_home::class, 'check_token']);
Route::get('/check_version', [C_home::class, 'checkLaravelVersion']);
Route::get('/check_db', [C_home::class, 'check_db']);
Route::get('/z_download/{filename}', [C_home::class, 'download_file']);
Route::post('/dashboard/dashboardguests', [C_home::class, 'dashboardguests']);
Route::middleware(['jwt'])->group(function () {
    Route::post('/reload', [C_home::class, 'reload']);
    Route::post('/{A}/{B}', [C_home::class, 'general']);
});
