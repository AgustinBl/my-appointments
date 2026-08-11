<?php

namespace App\Http\Controllers;

use App\Appointment;
use App\Specialty;
use App\User;
use App\WorkDay;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        $role = $user->role;
        $today = Carbon::today()->toDateString();

        $upcoming = $this->appointments()
            ->with(['specialty', 'doctor', 'patient'])
            ->whereIn('status', ['Reservada', 'Confirmada'])
            ->whereDate('schedule_date', '>=', $today)
            ->orderBy('schedule_date')
            ->orderBy('schedule_time')
            ->take(6)
            ->get();

        $metrics = $this->metricsFor($role, $today);
        $pendingCount = $this->appointments()->where('status', 'Reservada')->count();
        $patientCount = User::patients()->count();

        return view('home', compact('role', 'metrics', 'upcoming', 'pendingCount', 'patientCount'));
    }

    /** Consulta de citas acotada a lo que el usuario puede ver. */
    private function appointments()
    {
        $user = auth()->user();
        $query = Appointment::query();

        if ($user->role == 'doctor') {
            $query->where('doctor_id', $user->id);
        } elseif ($user->role == 'patient') {
            $query->where('patient_id', $user->id);
        }

        return $query;
    }

    /**
     * Cuatro métricas por rol. Solo se tiñe la accionable: la que espera
     * que alguien haga algo.
     */
    private function metricsFor($role, $today)
    {
        $todayCount = $this->appointments()
            ->whereIn('status', ['Reservada', 'Confirmada', 'Atendida'])
            ->whereDate('schedule_date', $today)
            ->count();

        $attendedToday = $this->appointments()
            ->where('status', 'Atendida')
            ->whereDate('schedule_date', $today)
            ->count();

        $pending = $this->appointments()->where('status', 'Reservada')->count();

        $pendingToday = $this->appointments()
            ->where('status', 'Reservada')
            ->whereDate('schedule_date', $today)
            ->count();

        if ($role == 'admin') {
            return [
                ['value' => $todayCount, 'label' => 'Citas de hoy', 'tone' => 'green',
                 'hint' => $attendedToday ? $attendedToday.' ya atendidas' : 'Ninguna atendida todavía'],

                ['value' => $pending, 'label' => 'Por confirmar', 'tone' => 'accent',
                 'hint' => $pendingToday ? $pendingToday.' son para hoy' : 'Ninguna es para hoy'],

                ['value' => User::doctors()->count(), 'label' => 'Médicos', 'tone' => 'plain',
                 'hint' => $this->doctorsWithSchedule().' con horario cargado'],

                ['value' => Specialty::count(), 'label' => 'Especialidades', 'tone' => 'plain',
                 'hint' => $this->topSpecialty()],
            ];
        }

        if ($role == 'doctor') {
            return [
                ['value' => $todayCount, 'label' => 'Citas de hoy', 'tone' => 'green',
                 'hint' => $attendedToday ? $attendedToday.' ya atendidas' : 'En tu agenda de hoy'],

                ['value' => $pending, 'label' => 'Por confirmar', 'tone' => 'accent',
                 'hint' => $pendingToday ? $pendingToday.' son para hoy' : 'Esperan tu respuesta'],

                ['value' => $this->appointments()->where('status', 'Atendida')->count(),
                 'label' => 'Atendidas', 'tone' => 'plain', 'hint' => 'Histórico completo'],

                ['value' => $this->appointments()->distinct()->count('patient_id'),
                 'label' => 'Pacientes', 'tone' => 'plain', 'hint' => 'Distintos, alguna vez atendidos'],
            ];
        }

        $next = $this->appointments()
            ->whereIn('status', ['Reservada', 'Confirmada'])
            ->whereDate('schedule_date', '>=', $today)
            ->orderBy('schedule_date')->orderBy('schedule_time')
            ->first();

        return [
            ['value' => $this->appointments()
                    ->whereIn('status', ['Reservada', 'Confirmada'])
                    ->whereDate('schedule_date', '>=', $today)->count(),
             'label' => 'Próximas citas', 'tone' => 'green',
             'hint' => $next ? 'La más próxima, '.$next->relative_label : 'De hoy en adelante'],

            ['value' => $pending, 'label' => 'Por confirmar', 'tone' => 'accent',
             'hint' => $pending ? 'El consultorio todavía no respondió' : 'Ninguna esperando'],

            ['value' => $this->appointments()->where('status', 'Atendida')->count(),
             'label' => 'Atendidas', 'tone' => 'plain', 'hint' => 'Consultas ya realizadas'],

            ['value' => $this->appointments()->where('status', 'Cancelada')->count(),
             'label' => 'Canceladas', 'tone' => 'plain', 'hint' => 'Por vos o por el consultorio'],
        ];
    }

    private function doctorsWithSchedule()
    {
        return WorkDay::where('active', true)->distinct()->count('user_id');
    }

    private function topSpecialty()
    {
        $top = Appointment::selectRaw('specialty_id, COUNT(*) as total')
            ->groupBy('specialty_id')
            ->orderByDesc('total')
            ->first();

        if (! $top) {
            return 'Todavía sin citas cargadas';
        }

        $specialty = Specialty::find($top->specialty_id);

        return $specialty ? $specialty->name.' es la más pedida' : 'Disponibles para reservar';
    }
}
