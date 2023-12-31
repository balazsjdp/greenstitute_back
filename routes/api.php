<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CertificationController;
use App\Http\Controllers\EmailController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);    
    Route::get('/check-token', [AuthController::class, 'checkToken']);
    Route::get('/user-profile/{id}', [AuthController::class, 'userProfileById']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'certification'
], function ($router) {
    Route::post('/requestCert', [CertificationController::class, 'certificationRequest']);
    Route::post('/certDocumentsUpload', [CertificationController::class, 'uploadDocuments']);
    Route::get('/queryCert', [CertificationController::class, 'query']);
    Route::get('/hasCertRequest', [CertificationController::class, 'userRequestedCert']);
    Route::get('/all', [CertificationController::class, 'all']);
    Route::post('/approve', [CertificationController::class, 'approve']);
    Route::post('/discard', [CertificationController::class, 'discard']);
    Route::post('/removeFiles', [CertificationController::class, 'removeFiles']);
});

Route::group([
    'middleware' => 'api',
    'prefix' => 'contact'
], function ($router) {
    Route::post('/', [EmailController::class, 'send']);
});