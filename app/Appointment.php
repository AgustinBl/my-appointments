<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Appointment extends Model
{
    protected $fillable = [
    	'description',
    	'specialty_id',
    	'doctor_id',
    	'patient_id',
    	'schedule_date',
    	'schedule_time',
    	'type'
    ];

    protected $hidden = [
        'specialty_id', 'doctor_id', 'schedule_time'
    ];

    protected $appends = [
        'schedule_time_12'
    ];

    // $appointment->specialty
    public function specialty()
    {
    	return $this->belongsTo(Specialty::class);
    }

    // $appointment->doctor
    public function doctor()
    {
    	return $this->belongsTo(User::class);
    }

    // $appointment->patient
    public function patient()
    {
    	return $this->belongsTo(User::class);
    }

    public function cancellation()
    {
        return $this->hasOne(CancelledAppointment::class);
    }

    // accessor
    // $appointment->schedule_time_12
    public function getScheduleTime12Attribute() {
    	return (new Carbon($this->schedule_time))->format('g:i A');
    }
}
