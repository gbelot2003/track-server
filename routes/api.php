<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/**
 * Rutas de autentificación del sistema
 */
Route::post('v1/login', 'Auth\AuthController@login');
Route::post('v1/register', 'Auth\AuthController@register');
Route::middleware('auth:api')->get('v1/user', 'Auth\AuthController@user');
Route::middleware('auth:api')->post('v1/logout', 'Auth\AuthController@logout');




