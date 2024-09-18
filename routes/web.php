<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ToDoController;

Route::get('/', [ToDoController::class, 'index']);
Route::post('/save-task', [ToDoController::class, 'store'])->name("save-task");
Route::post('/update-task', [ToDoController::class, 'update'])->name("update-task-status");
Route::delete('/delete-task', [ToDoController::class, 'delete'])->name("delete-task");
