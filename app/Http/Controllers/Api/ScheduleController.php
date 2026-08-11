<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Interfaces\ScheduleServiceInterface;

use App\User;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    /** 0 = lunes, como en work_days.day */
    private $dayNames = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'];
    private $dayShort = ['lun', 'mar', 'mié', 'jue', 'vie', 'sáb', 'dom'];
    private $monthShort = ['', 'ene', 'feb', 'mar', 'abr', 'may', 'jun',
                           'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

    public function hours(Request $request, ScheduleServiceInterface $scheduleService)
    {
        $date = $request->input('date');
        $doctorId = $request->input('doctor_id');

        if (! $date || ! $doctorId) {
            return ['attends' => false, 'reason' => 'incomplete'];
        }

        $data = $scheduleService->getDayIntervals($date, $doctorId);

        // Si ese día no atiende, el aviso tiene que ofrecer la salida:
        // qué días sí atiende y las próximas dos fechas válidas.
        if (! $data['attends']) {
            $activeDays = $scheduleService->activeDays($doctorId);

            $data['reason'] = count($activeDays) ? 'closed' : 'no-schedule';
            $data['doctor'] = optional(User::find($doctorId))->name;
            $data['dayLabel'] = $this->longDate($date);
            $data['days'] = $this->daysSentence($activeDays);
            $data['next'] = $this->nextDates($activeDays, $date);
        }

        return $data;
    }

    /** «martes 11 de agosto» */
    private function longDate($date)
    {
        $months = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio',
                   'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        $carbon = new Carbon($date);

        return $this->dayNames[$this->weekIndex($carbon)].' '.$carbon->day.' de '.$months[$carbon->month];
    }

    /** «jueves», «lunes y jueves», «lunes, miércoles y viernes» */
    private function daysSentence(array $days)
    {
        $names = array_map(function ($day) { return $this->dayNames[$day]; }, $days);

        if (count($names) <= 1) {
            return implode('', $names);
        }

        $last = array_pop($names);

        return implode(', ', $names).' y '.$last;
    }

    /** Las próximas dos fechas en que sí atiende, dentro de los 30 días. */
    private function nextDates(array $days, $from)
    {
        if (! count($days)) {
            return [];
        }

        $cursor = (new Carbon($from))->addDay();
        $limit = Carbon::today()->addDays(30);
        $found = [];

        while ($cursor <= $limit && count($found) < 2) {
            if (in_array($this->weekIndex($cursor), $days)) {
                $found[] = [
                    'date'  => $cursor->toDateString(),
                    'label' => $this->dayShort[$this->weekIndex($cursor)].' '
                               .$cursor->day.' '.$this->monthShort[$cursor->month],
                ];
            }
            $cursor->addDay();
        }

        return $found;
    }

    /** Carbon usa 0 = domingo; work_days usa 0 = lunes. */
    private function weekIndex(Carbon $date)
    {
        return $date->dayOfWeek == 0 ? 6 : $date->dayOfWeek - 1;
    }
}
