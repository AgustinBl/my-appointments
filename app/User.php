<?php

namespace App;

use App\Services\Fcm;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'dni', 'address', 'phone', 'role'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'pivot','email_verified_at', 'created_at', 'updated_at'
    ];

    public static $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6|confirmed',
    ];

    public static function createPatient(array $data)
    {
        return self::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'patient'
        ]);
    }

    public function specialties()
    {
        return $this->belongsToMany(Specialty::class)->withTimestamps();
    }
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function scopePatients($query)
    {
        return $query->where('role', 'patient');
    }

    public function scopeDoctors($query)
    {
        return $query->where('role', 'doctor');
    }

    public function asDoctorAppointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    public function attendedAppointments()
    {
        return $this->asDoctorAppointments()->where('status', 'Atendida');
    }

    public function cancelledAppointments()
    {
        return $this->asDoctorAppointments()->where('status', 'Cancelada');
    }
    
    public function asPatientAppointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    public function workDays()
    {
        return $this->hasMany(WorkDay::class);
    }

    /**
     * Días en que el médico atiende, para el listado: «Lun y jue».
     * Vacío significa que todavía no puede recibir reservas.
     */
    public function getScheduleLabelAttribute()
    {
        $days = $this->workDays->where('active', true)->pluck('day')->sort()->values()->all();

        return WorkDay::daysSentence($days, true);
    }

    /**
     * Envia una notificacion push al dispositivo del usuario.
     *
     * Devuelve false (sin lanzar excepciones) si el usuario no tiene token o
     * si Firebase rechaza el envio, para no cortar el flujo que la disparó.
     */
    public function sendFCM($message, array $data = [])
    {
        if (empty($this->device_token)) {
            return false;
        }

        $result = app(Fcm::class)->sendToToken(
            $this->device_token,
            config('app.name'),
            $message,
            $data
        );

        // El token dejo de ser valido (app desinstalada o token rotado):
        // lo borramos para no seguir intentando contra un destino muerto.
        if ($result['invalid_token']) {
            $this->device_token = null;
            $this->save();
        }

        return $result['success'];
    }
}

