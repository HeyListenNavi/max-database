<?php

use App\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;

// 1. Pantalla de inicio: Lista de Alumnos para elegir
Route::get('/', [ScheduleController::class, 'chooseStudent'])->name('home');

// 2. Pantalla de selección de materias (específica para un alumno)
Route::get('/student/{user}/schedule', [ScheduleController::class, 'index'])->name('schedule.index');

// 3. Procesar y Generar Horario
Route::post('/student/{user}/generate', [ScheduleController::class, 'generate'])->name('schedule.generate');