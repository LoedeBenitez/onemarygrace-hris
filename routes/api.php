<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

#region Announcement
Route::post('announcement/create', [App\Http\Controllers\Dashboard\AnnouncementController::class, 'onCreate']);
Route::post('announcement/update/{id}', [App\Http\Controllers\Dashboard\AnnouncementController::class, 'onUpdateById']);
Route::post('announcement/get', [App\Http\Controllers\Dashboard\AnnouncementController::class, 'onGetPaginatedList']);
Route::post('announcement/get/all', [App\Http\Controllers\Dashboard\AnnouncementController::class, 'onGetAll']);
Route::post('announcement/get/{id}', [App\Http\Controllers\Dashboard\AnnouncementController::class, 'onGetById']);
Route::post('announcement/delete/{id}', [App\Http\Controllers\Dashboard\AnnouncementController::class, 'onDeleteById']);
#endregion

#region Events
Route::post('event/create', [App\Http\Controllers\Dashboard\EventController::class, 'onCreate']);
Route::post('event/update/{id}', [App\Http\Controllers\Dashboard\EventController::class, 'onUpdateById']);
Route::post('event/get', [App\Http\Controllers\Dashboard\EventController::class, 'onGetPaginatedList']);
Route::post('event/get/all', [App\Http\Controllers\Dashboard\EventController::class, 'onGetAll']);
Route::post('event/get/{id}', [App\Http\Controllers\Dashboard\EventController::class, 'onGetById']);
Route::post('event/delete/{id}', [App\Http\Controllers\Dashboard\EventController::class, 'onDeleteById']);
#endregion

#region Holidays
Route::post('holiday/create', [App\Http\Controllers\Dashboard\HolidayController::class, 'onCreate']);
Route::post('holiday/update/{id}', [App\Http\Controllers\Dashboard\HolidayController::class, 'onUpdateById']);
Route::post('holiday/get', [App\Http\Controllers\Dashboard\HolidayController::class, 'onGetPaginatedList']);
Route::post('holiday/get/all', [App\Http\Controllers\Dashboard\HolidayController::class, 'onGetAll']);
Route::post('holiday/get/{id}', [App\Http\Controllers\Dashboard\HolidayController::class, 'onGetById']);
Route::post('holiday/delete/{id}', [App\Http\Controllers\Dashboard\HolidayController::class, 'onDeleteById']);
#endregion
