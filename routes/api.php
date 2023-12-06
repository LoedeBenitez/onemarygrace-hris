<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

#region Announcement
Route::post('announcement/create', [App\Http\Controllers\Dashboard\AnnouncementController::class, 'onCreate']);
Route::patch('announcement/update/{id}', [App\Http\Controllers\Dashboard\AnnouncementController::class, 'onUpdateById']);
Route::post('announcement/get', [App\Http\Controllers\Dashboard\AnnouncementController::class, 'onGetPaginatedList']);
Route::get('announcement/get/all', [App\Http\Controllers\Dashboard\AnnouncementController::class, 'onGetAll']);
Route::get('announcement/get/{id}', [App\Http\Controllers\Dashboard\AnnouncementController::class, 'onGetById']);
Route::delete('announcement/delete/{id}', [App\Http\Controllers\Dashboard\AnnouncementController::class, 'onDeleteById']);
#endregion

#region Events
Route::post('event/create', [App\Http\Controllers\Dashboard\EventController::class, 'onCreate']);
Route::patch('event/update/{id}', [App\Http\Controllers\Dashboard\EventController::class, 'onUpdateById']);
Route::post('event/get', [App\Http\Controllers\Dashboard\EventController::class, 'onGetPaginatedList']);
Route::get('event/get/all', [App\Http\Controllers\Dashboard\EventController::class, 'onGetAll']);
Route::get('event/get/{id}', [App\Http\Controllers\Dashboard\EventController::class, 'onGetById']);
Route::delete('event/delete/{id}', [App\Http\Controllers\Dashboard\EventController::class, 'onDeleteById']);
#endregion

#region Holidays
Route::post('holiday/create', [App\Http\Controllers\Dashboard\HolidayController::class, 'onCreate']);
Route::patch('holiday/update/{id}', [App\Http\Controllers\Dashboard\HolidayController::class, 'onUpdateById']);
Route::post('holiday/get', [App\Http\Controllers\Dashboard\HolidayController::class, 'onGetPaginatedList']);
Route::get('holiday/get/all', [App\Http\Controllers\Dashboard\HolidayController::class, 'onGetAll']);
Route::get('holiday/get/{id}', [App\Http\Controllers\Dashboard\HolidayController::class, 'onGetById']);
Route::delete('holiday/delete/{id}', [App\Http\Controllers\Dashboard\HolidayController::class, 'onDeleteById']);
#endregion

#region Memoranda
Route::post('memoranda/create', [App\Http\Controllers\Dashboard\MemorandaController::class, 'onCreate']);
Route::patch('memoranda/update/{id}', [App\Http\Controllers\Dashboard\MemorandaController::class, 'onUpdateById']);
Route::post('memoranda/get', [App\Http\Controllers\Dashboard\MemorandaController::class, 'onGetPaginatedList']);
Route::get('memoranda/get/all', [App\Http\Controllers\Dashboard\MemorandaController::class, 'onGetAll']);
Route::get('memoranda/get/{id}', [App\Http\Controllers\Dashboard\MemorandaController::class, 'onGetById']);
Route::delete('memoranda/delete/{id}', [App\Http\Controllers\Dashboard\MemorandaController::class, 'onDeleteById']);
#endregion
