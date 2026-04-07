<?php

use App\Http\Controllers\StickyNoteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StickyNoteController::class, 'index']);

Route::post('/notes', [StickyNoteController::class, 'store']);
Route::patch('/notes/{stickyNote}', [StickyNoteController::class, 'update']);
Route::delete('/notes/{stickyNote}', [StickyNoteController::class, 'destroy']);
