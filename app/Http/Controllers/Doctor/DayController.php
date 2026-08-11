<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Appointment;
use App\WorkDay;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * «Mi día»: la agenda del médico puesta sobre el riel de franjas de 30 minutos.
 *
 * No usa datos nuevos. Es la misma información de la tabla de citas, cruzada
 * con el horario de atención que el médico ya tiene cargado.
 */
class DayController extends Controller
{
    private $months = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
                       'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
    private $weekdays = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

    public function index(Request $request)
    {
        $date = $this->resolveDate($request->input('date'));
        $workDay = $this->workDay($date);

        $appointments = Appointment::with('patient')
            ->where('doctor_id', auth()->id())
            ->whereDate('schedule_date', $date->toDateString())
            ->whereIn('status', ['Reservada', 'Confirmada'])
            ->get()
            ->keyBy(function ($appointment) {
                return (new Carbon($appointment->schedule_time))->format('H:i');
            });

        $rows = $this->buildRail($workDay, $appointments);

        $summary = [
            'citas'   => $appointments->count(),
            'pending' => $appointments->where('status', 'Reservada')->count(),
            'free'    => collect($rows)->where('type', 'free')->count(),
        ];

        $pending = $appointments->where('status', 'Reservada')->values();

        $header = [
            'weekday' => $this->weekdays[$this->weekIndex($date)],
            'long'    => $this->weekdays[$this->weekIndex($date)].' '.$date->day.' de '.$this->months[$date->month],
            'ranges'  => $this->rangesLabel($workDay),
            'isToday' => $date->isSameDay(Carbon::today()),
            'date'    => $date->toDateString(),
            'prev'    => $date->copy()->subDay()->toDateString(),
            'next'    => $date->copy()->addDay()->toDateString(),
        ];

        return view('doctor.day', compact('rows', 'summary', 'pending', 'header', 'workDay'));
    }

    /**
     * Confirma de una vez las citas pendientes del día que se está viendo.
     */
    public function confirmAll(Request $request)
    {
        $date = $this->resolveDate($request->input('date'));

        $pending = Appointment::with('patient')
            ->where('doctor_id', auth()->id())
            ->whereDate('schedule_date', $date->toDateString())
            ->where('status', 'Reservada')
            ->get();

        foreach ($pending as $appointment) {
            $appointment->status = 'Confirmada';

            if ($appointment->save()) {
                $appointment->patient->sendFCM('Su cita se ha confirmado!');
            }
        }

        $count = $pending->count();
        $notification = $count
            ? 'Confirmaste '.$count.($count == 1 ? ' cita. Le avisamos al paciente.' : ' citas. Les avisamos a los pacientes.')
            : 'No quedaban citas por confirmar en ese día.';

        return redirect('/my-day?date='.$date->toDateString())->with(compact('notification'));
    }

    /* --------------------------------------------------------------------- */

    private function resolveDate($input)
    {
        try {
            return $input ? Carbon::createFromFormat('Y-m-d', $input)->startOfDay() : Carbon::today();
        } catch (\Exception $e) {
            return Carbon::today();
        }
    }

    private function workDay(Carbon $date)
    {
        return WorkDay::where('user_id', auth()->id())
            ->where('day', $this->weekIndex($date))
            ->where('active', true)
            ->first();
    }

    /**
     * Filas del riel: cada franja de 30 minutos de los dos turnos, más una
     * fila que marca el corte entre mañana y tarde.
     */
    private function buildRail($workDay, $appointments)
    {
        if (! $workDay) {
            return [];
        }

        $rows = $this->slotRows($workDay->morning_start, $workDay->morning_end, $appointments);

        $gap = $this->gapRow($workDay);
        if ($gap) {
            $rows[] = $gap;
        }

        return array_merge($rows, $this->slotRows($workDay->afternoon_start, $workDay->afternoon_end, $appointments));
    }

    private function slotRows($start, $end, $appointments)
    {
        if (! $start || ! $end) {
            return [];
        }

        $cursor = new Carbon($start);
        $limit = new Carbon($end);
        $rows = [];

        while ($cursor < $limit) {
            $key = $cursor->format('H:i');
            $appointment = $appointments->get($key);

            $rows[] = [
                'time'        => $cursor->format('G:i'),
                'type'        => $appointment
                    ? ($appointment->status == 'Confirmada' ? 'confirmed' : 'pending')
                    : 'free',
                'appointment' => $appointment,
            ];

            $cursor->addMinutes(30);
        }

        return $rows;
    }

    private function gapRow($workDay)
    {
        if (! $workDay->morning_end || ! $workDay->afternoon_start) {
            return null;
        }

        $from = new Carbon($workDay->morning_end);
        $to = new Carbon($workDay->afternoon_start);

        if ($from >= $to) {
            return null;
        }

        return [
            'time'  => $from->format('G:i'),
            'type'  => 'off',
            'range' => $from->format('G:i').' – '.$to->format('G:i'),
        ];
    }

    private function rangesLabel($workDay)
    {
        if (! $workDay) {
            return null;
        }

        $parts = [];

        foreach ([['morning_start', 'morning_end'], ['afternoon_start', 'afternoon_end']] as $pair) {
            if ($workDay->{$pair[0]} && $workDay->{$pair[1]}) {
                $parts[] = (new Carbon($workDay->{$pair[0]}))->format('G:i')
                         .' – '.(new Carbon($workDay->{$pair[1]}))->format('G:i');
            }
        }

        return implode(' y ', $parts);
    }

    /** Carbon usa 0 = domingo; work_days usa 0 = lunes. */
    private function weekIndex(Carbon $date)
    {
        return $date->dayOfWeek == 0 ? 6 : $date->dayOfWeek - 1;
    }
}
