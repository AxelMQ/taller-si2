<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CarController;
use App\Http\Controllers\Api\SolicitudController;
use App\Http\Controllers\Api\TecnicoController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//CRUD USER-CLIENTE-TECNICO-TALLER
Route::post('user-cliente', [UserController::class, 'registrarCliente']);
Route::middleware('auth:sanctum')->post('user-tecnico', [UserController::class, 'registrarTecnico']);
Route::post('user-taller', [UserController::class, 'registerTaller']);
Route::get('users', [UserController::class, 'allUsers']);

//Autenticacion
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->get('user-info', [AuthController::class, 'userInfo']);

//Car
Route::get('cars',[CarController::class, 'getCars']);
Route::delete('car-delete/{id}', [CarController::class, 'deleteCar']);
Route::middleware('auth:sanctum')->get('car-user', [CarController::class, 'getCarUser']);
Route::middleware('auth:sanctum')->post('register-car', [CarController::class, 'registerCar']);

//tecnico
Route::get('tecnicos',[TecnicoController::class, 'getTecnicos']);
Route::middleware('auth:sanctum')->get('tecnico-taller', [TecnicoController::class, 'getTecnicoTaller']);
Route::middleware('auth:sanctum')->post('tecnico-update', [TecnicoController::class, 'updateTecnico']);
Route::delete('tecnico-delete/{id}', [TecnicoController::class, 'deleteTecnico']);

//solicitud
Route::get('solicitudes',[SolicitudController::class, 'getAllSolicitudes']);
Route::get('solicitudes-proceso',[SolicitudController::class, 'getSolicitudesEnProceso']);
Route::get('solicitud/{id}',[SolicitudController::class, 'getSolicitudPorId']);
Route::middleware('auth:sanctum')->post('solicitud-register', [SolicitudController::class, 'registerSolicitud']);


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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
