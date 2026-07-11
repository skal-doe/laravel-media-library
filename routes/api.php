<?php

use Illuminate\Support\Facades\Route;
use SkalDoe\MediaLibrary\Http\Controllers\MediaController;
use SkalDoe\MediaLibrary\Http\Controllers\MediaFolderController;

Route::apiResource('medias', MediaController::class)->except('show')->middleware(config('media-library.middleware'));
Route::apiResource('folders', MediaFolderController::class)->except('show')->middleware(config('media-library.middleware'));