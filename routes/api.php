<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\EmployeeLeaveController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ImageController;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::any('/', function () {
    return "Welcome to " . env('APP_NAME') . " Backend";
});

Route::prefix('v1')->group(function () {
    //User - Unprotected
    Route::controller(UserController::class)->prefix('user')->group(function () {
        Route::post('/login',  'login');
        Route::post('/register', 'register');
        Route::get('/verify/{id}/{hash}', 'verifyEmail')->name('verification.verify');
        Route::post('/resendVerificationEmail', 'resendVerificationEmail');
    });

    //Reset Password
    Route::controller(PasswordResetController::class)->prefix('user/password')->group(function () {
        Route::post('/requestPasswordReset',  'requestPasswordReset');
        Route::get('/findPasswordResetToken',  'findPasswordResetToken');
        Route::post('/resetPassword',  'resetPassword');
    });
});

Route::group(['prefix'=>'v1','middleware' => 'auth:sanctum'], function() {
    //User
    Route::controller(UserController::class)->prefix('user')->group(function () {
        Route::get('/get', 'getUserDetails');
        Route::get('/show/{id}/{onlyActive?}', 'show');
        Route::get('/index/{onlyActive?}', 'index');
        Route::post('/update',  'update');
        Route::post('/changePassword', 'changePassword');
        Route::post('/logout',  'logout');
        Route::post('/changeStatus', 'changeStatus');
    });

    Route::controller(EmployeeController::class)->prefix('employee')->group(function () {
        Route::get('/show/{id}/{onlyActive?}', 'show');
        Route::get('/index/{onlyActive?}', 'index');
        Route::get('/getEmployeeLeavesList', 'getEmployeeLeavesList');
        Route::get('/getEmployeeTransactionLogs', 'getEmployeeTransactionLogs');
        Route::post('/create',  'store');
        Route::post('/update',  'update');
        Route::post('/moveToDepartment',  'moveToDepartment');
        Route::post('/incrementSalary',  'incrementSalary');
        Route::post('/changeJobTitle',  'changeJobTitle');
        Route::post('/terminateEmployeeServices',  'terminateEmployeeServices');
        Route::post('/disableSelfServices',  'disableSelfServices');
        Route::post('/enableSelfServices',  'enableSelfServices');
    });

    Route::controller(EmployeeLeaveController::class)->prefix('employeeLeave')->group(function () {
        Route::get('/getManagerLeaveRequests', 'getManagerLeaveRequests');
        Route::get('/getLeaveBalance', 'getLeaveBalance');
        Route::post('/applyForLeave', 'applyForLeave');
        Route::post('/withdrawLeave', 'withdrawLeave');
        Route::post('/actionLeave', 'actionLeave');
    });

    Route::controller(DepartmentController::class)->prefix('department')->group(function () {
        Route::get('/show/{id}/{onlyActive?}', 'show');
        Route::get('/index/{onlyActive?}', 'index');
        Route::post('/create',  'store');
        Route::post('/update',  'update');
        Route::post('/assignManager', 'assignManager');
        Route::post('/activate', 'activate');
        Route::post('/deactivate', 'deactivate');
    });

    Route::controller(LeaveController::class)->prefix('leave')->group(function () {
        Route::get('/show/{id}/{onlyActive?}', 'show');
        Route::get('/index/{onlyActive?}', 'index');
        Route::post('/create',  'store');
        Route::post('/update',  'update');
        Route::post('/activate', 'activate');
        Route::post('/deactivate', 'deactivate');
    });

    Route::controller(ImageController::class)->prefix('image')->group(function () {
        Route::post('/uploadImage', 'uploadImage');
        Route::post('/deleteImage', 'deleteImage');
    });

});

//Fallback - Unprotected
Route::fallback(function(){
    return response()->json(['message' => 'Endpoint not found in this project'], ResponseAlias::HTTP_NOT_FOUND);
});
