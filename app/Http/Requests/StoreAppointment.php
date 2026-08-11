<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Interfaces\ScheduleServiceInterface;
use Illuminate\Validation\Rule;
use Carbon\Carbon;


class StoreAppointment extends FormRequest
{
    private $scheduleService;

    public function __construct(ScheduleServiceInterface $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'description' => 'required',
            'specialty_id' => 'exists:specialties,id',
            // No alcanza con que el id exista en `users`: tiene que ser un
            // medico. Antes se podia reservar contra cualquier usuario.
            'doctor_id' => [
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', 'doctor');
                }),
            ],
            'schedule_time' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'description.required'   => 'Contale al médico el motivo de la consulta.',
            'schedule_time.required' => 'Elegí una franja horaria para tu cita.',
            'specialty_id.exists'    => 'Esa especialidad no existe. Elegí una de la lista.',
            'doctor_id.exists'       => 'Ese médico no existe. Elegí uno de la lista.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validatePatientRole($validator);

            $date = $this->input('schedule_date');
            $doctorId = $this->input('doctor_id');
            $schedule_time = $this->input('schedule_time');
            if(!$date || !$doctorId || !$schedule_time) {
                return;
            }

            $start = new Carbon($schedule_time);

            if(!$this->scheduleService->isAvaliableInterval($date, $doctorId, $start)) {
                $validator->errors()->add('avaliable_time', 'La hora seleccionada ya se encuentra reservada.');
            }
        });
    }

    /**
     * Una cita se crea siempre a nombre de quien la pide, asi que quien la pide
     * tiene que ser paciente. Sin esto, un medico autenticado podia reservarse
     * turnos —incluso consigo mismo— porque la unica barrera era estar logueado.
     *
     * Va como error de validacion y no como `authorize()` a proposito: asi el
     * rechazo viaja en el 422 con el resto de los errores y el cliente movil
     * puede mostrar este mensaje, en lugar del 403 generico.
     */
    private function validatePatientRole($validator)
    {
        $user = $this->user('api') ?: $this->user();

        if ($user && $user->role !== 'patient') {
            $validator->errors()->add(
                'role',
                'Solo los pacientes pueden reservar citas.'
            );
        }
    }
}
