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
    return redirect('/login');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::middleware(['auth', 'admin'])->namespace('Admin')->group(function () {
	// Specialty
	Route::get('/specialties', 'SpecialtyController@index');
	Route::get('/specialties/create', 'SpecialtyController@create'); // form registro
	Route::get('/specialties/{specialty}/edit', 'SpecialtyController@edit');
	Route::post('/specialties', 'SpecialtyController@store'); // envio del form
	Route::put('/specialties/{specialty}', 'SpecialtyController@update');
	Route::delete('/specialties/{specialty}', 'SpecialtyController@destroy');
	// Doctors
	Route::resource('doctors', 'DoctorController');
	// Patients
	Route::resource('patients', 'PatientController');

	// FCM
	Route::post('/fcm/send', 'FirebaseController@sendAll');

});

Route::middleware(['auth', 'doctor'])->namespace('Doctor')->group(function () {
	Route::get('/schedule', 'ScheduleController@edit');
	Route::post('/schedule', 'ScheduleController@store');

});

Route::middleware('auth')->group(function () {
	Route::get('/appointments/create', 'AppointmentController@create');
	Route::post('/appointments', 'AppointmentController@store');
	
	Route::get('/appointments', 'AppointmentController@index');
	Route::get('/appointments/{appointment}', 'AppointmentController@show');


	Route::get('/appointments/{appointment}/cancel', 'AppointmentController@showCancelForm');
	Route::post('/appointments/{appointment}/cancel', 'AppointmentController@postCancel');

	Route::post('/appointments/{appointment}/confirm', 'AppointmentController@postConfirm');

	
	

});



