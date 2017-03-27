<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/signin', 'AuthController@signin');
Route::get('/authorize', 'AuthController@gettoken');
Route::get('/mail', 'OutlookController@mail')->name('mail');
Route::get('/calendar', 'OutlookController@calendar')->name('calendar');
Route::get('/contacts', 'OutlookController@contacts')->name('contacts');