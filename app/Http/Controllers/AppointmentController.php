<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Specialty;
use App\Appointment;
use App\CancelledAppointment;
use Carbon\Carbon;
use App\Interfaces\ScheduleServiceInterface;
use App\Http\Requests\StoreAppointment;
use Validator;

class AppointmentController extends Controller
{
    public function index()
    {
        $role = auth()->user()->role;

        $confirmedAppointments = $this->scoped($role, 'Confirmada')->paginate(10);
        $pendingAppointments   = $this->scoped($role, 'Reservada')->paginate(10);
        $oldAppointments       = $this->scoped($role, ['Atendida', 'Cancelada'])->paginate(10);

        return view('appointments.index',
            compact('pendingAppointments', 'confirmedAppointments', 'oldAppointments', 'role'));
    }

    /**
     * Citas visibles para el rol, filtradas por estado y ordenadas por fecha.
     */
    private function scoped($role, $status)
    {
        $query = Appointment::with(['specialty', 'doctor', 'patient'])
            ->orderBy('schedule_date')
            ->orderBy('schedule_time');

        is_array($status)
            ? $query->whereIn('status', $status)
            : $query->where('status', $status);

        if ($role == 'doctor') {
            $query->where('doctor_id', auth()->id());
        } elseif ($role == 'patient') {
            $query->where('patient_id', auth()->id());
        }

        return $query;
    }

    public function show(Appointment $appointment)
    {
        $role = auth()->user()->role;
        return view('appointments.show', compact('appointment', 'role'));
    }

    public function create(ScheduleServiceInterface $scheduleService)
    {
    	$specialties = Specialty::orderBy('name')->get();

        $specialtyId = old('specialty_id');
        if ($specialtyId) {
            $specialty = Specialty::find($specialtyId);
            $doctors = $specialty ? $specialty->users : collect();
        } else {
            $doctors = collect();
        }

        $date = old('schedule_date');
        $doctorId = old('doctor_id');

        // Franjas del día, con las tomadas incluidas para poder mostrarlas tachadas.
        $day = ($date && $doctorId)
            ? $scheduleService->getDayIntervals($date, $doctorId)
            : null;

    	return view('appointments.create', compact('specialties', 'doctors', 'day'));
    }

    public function store(StoreAppointment $request)
    {
        Appointment::createForPatient($request, auth()->id());

    	$notification = 'Reservamos tu cita. Queda por confirmar hasta que el consultorio la confirme.';

    	return redirect('/appointments')->with(compact('notification'));
    }

    public function showCancelForm(Appointment $appointment)
    {
        if($appointment->status == 'Confirmada'){
            $role = auth()->user()->role;
            return view('appointments.cancel', compact('appointment', 'role'));
        }

        return redirect('/appointments');
    }

    public function postCancel(Appointment $appointment, Request $request)
    {
        if($request->has('justification')){
            $cancellation = new CancelledAppointment();
            $cancellation->justification = $request->input('justification');
            $cancellation->cancelled_by_id = auth()->id();

            $appointment->cancellation()->save($cancellation);
        }

        $appointment->status = 'Cancelada';
        $saved = $appointment->save();

        if($saved)
            $appointment->patient->sendFCM('Su cita se ha cancelado!');

        $notification = 'La cita quedó cancelada y la franja volvió a estar disponible.';
        return redirect('/appointments')->with(compact('notification'));
    }

    public function postConfirm(Appointment $appointment)
    {
        $appointment->status = 'Confirmada';
        $saved = $appointment->save();

        if($saved)
            $appointment->patient->sendFCM('Su cita se ha confirmado!');

        $notification = 'Cita confirmada. Le avisamos al paciente.';
        return back()->with(compact('notification'));
    }
}
