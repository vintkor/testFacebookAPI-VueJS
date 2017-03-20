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

Route::get('/', 'FacebookController@index')->name('home');
Route::get('/facebook/callback', 'FacebookController@callback')->name('callback');
Route::post('/facebook/callback', 'FacebookController@POSTcallback');

Auth::routes();

Route::get('/home', 'HomeController@index');
