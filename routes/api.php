<?php

use Illuminate\Support\Facades\Route;
use SkalDoe\MediaLibrary\Http\Controllers\MediaController;
use SkalDoe\MediaLibrary\Http\Controllers\MediaFolderController;

Route::apiResource('medias', MediaController::class)->except('show');
Route::apiResource('folders', MediaFolderController::class)->except(['index', 'show']);
