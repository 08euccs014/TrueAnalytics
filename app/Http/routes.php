<?php

use App\Http\Controllers\xyz;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/',  'backend@display');
Route::get('job',  'backend@display');
Route::get('job/inform',  'backend@inform');

Route::get('workstream',  'backend@workstream');
Route::get('revenue',  'backend@revenue');
Route::get('social',  'backend@social');

// Event::listen('illuminate.query', function($sql) {
// var_dump($sql);
// });
