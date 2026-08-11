<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

    // accessor
    // $appointment->schedule_day_label  =>  "jue 13 ago"
    public function getScheduleDayLabelAttribute()
    {
        $days = ['dom', 'lun', 'mar', 'mié', 'jue', 'vie', 'sáb'];
        $months = ['', 'ene', 'feb', 'mar', 'abr', 'may', 'jun',
                   'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

        $date = new Carbon($this->schedule_date);

        return $days[$date->dayOfWeek].' '.$date->day.' '.$months[$date->month];
    }

    // accessor
    // $appointment->relative_label  =>  "en 6 días"
    // Es la razón por la que el paciente entra al listado.
    public function getRelativeLabelAttribute()
    {
        $days = Carbon::today()->diffInDays(
            (new Carbon($this->schedule_date))->startOfDay(), false
        );

        if ($days === 0) return 'hoy';
        if ($days === 1) return 'mañana';
        if ($days === -1) return 'ayer';

        return $days > 0
            ? 'en '.$days.' días'
            : 'hace '.abs($days).' días';
    }

    // accessor
    // $appointment->schedule_time_short  =>  "8:30"
    public function getScheduleTimeShortAttribute()
    {
        return (new Carbon($this->schedule_time))->format('G:i');
    }

    static public function createForPatient(Request $request, $patientId)
    {
        $data = $request->only([
        'description',
        'specialty_id',
        'doctor_id',
        'schedule_date',
        'schedule_time',
        'type'
        ]);

        $data['patient_id'] = $patientId;

        $carbonTime = Carbon::createFromFormat('g:i A', $data['schedule_time']);
        $data['schedule_time'] = $carbonTime->format('H:i:s');

        return self::create($data);
    }
}
