<?php namespace App\Services;

use App\WorkDay;
use App\Interfaces\ScheduleServiceInterface;
use Carbon\Carbon;
use App\Appointment;

class ScheduleService implements ScheduleServiceInterface
{
	public function isAvaliableInterval($date, $doctorId, Carbon $start){
		$exists = Appointment::where('doctor_id', $doctorId)
    		->where('schedule_date', $date)
    		->where('schedule_time', $start->format('H:i:s'))
            ->where('status', '!=', 'Cancelada')
    		->exists();

    	return !$exists;
	}

	private function getDayFromDate($date)
	{
    	$dateCarbon = new Carbon($date);
    	$i = $dateCarbon->dayOfWeek;
    	$day = ($i==0 ? 6 : $i-1);
    	return $day;
	}

	public function getAvaliableIntervals($date, $doctorId)
	{
		$workDay = $this->workDayFor($date, $doctorId);

        if($workDay){
            $morningIntervals = $this->getIntervals(
                $workDay->morning_start, $workDay->morning_end, $date, $doctorId
            );

            $afternoonIntervals = $this->getIntervals(
                $workDay->afternoon_start, $workDay->afternoon_end, $date, $doctorId
            );
        } else {
            $morningIntervals = [];
            $afternoonIntervals = [];
       }

		$data = [];
		$data['morning'] = $morningIntervals;
		$data['afternoon'] = $afternoonIntervals;

		return $data;
	}

    /**
     * Todas las franjas del día, libres y tomadas, con el rango de cada turno.
     *
     * La UI muestra las tomadas tachadas en vez de ocultarlas: así el paciente
     * entiende que el médico sí atiende ese día y que esa hora ya está dada.
     */
    public function getDayIntervals($date, $doctorId)
    {
        $workDay = $this->workDayFor($date, $doctorId);

        if (! $workDay) {
            return ['attends' => false, 'morning' => null, 'afternoon' => null];
        }

        return [
            'attends'   => true,
            'morning'   => $this->shift($workDay->morning_start, $workDay->morning_end, $date, $doctorId),
            'afternoon' => $this->shift($workDay->afternoon_start, $workDay->afternoon_end, $date, $doctorId),
        ];
    }

    /**
     * Días de la semana (0 = lunes) en que el médico tiene atención cargada.
     */
    public function activeDays($doctorId)
    {
        return WorkDay::where('user_id', $doctorId)
            ->where('active', true)
            ->orderBy('day')
            ->pluck('day')
            ->all();
    }

    /**
     * Cuántas franjas de 30 minutos genera un día. Es el mismo cálculo que
     * alimenta la columna «Franjas», el gráfico y el total del encabezado.
     */
    public function slotsInDay(WorkDay $workDay)
    {
        if (! $workDay->active) {
            return 0;
        }

        return $this->countSlots($workDay->morning_start, $workDay->morning_end)
             + $this->countSlots($workDay->afternoon_start, $workDay->afternoon_end);
    }

    private function countSlots($start, $end)
    {
        if (! $start || ! $end) {
            return 0;
        }

        $start = new Carbon($start);
        $end = new Carbon($end);

        return $start < $end ? (int) floor($start->diffInMinutes($end) / 30) : 0;
    }

    private function workDayFor($date, $doctorId)
    {
        return WorkDay::where('active', true)
            ->where('day', $this->getDayFromDate($date))
            ->where('user_id', $doctorId)
            ->first([
                'morning_start', 'morning_end',
                'afternoon_start', 'afternoon_end',
            ]);
    }

    /**
     * Un turno: su rango legible, sus franjas y cuántas quedan libres.
     */
    private function shift($start, $end, $date, $doctorId)
    {
        $slots = [];
        $free = 0;

        if ($start && $end) {
            $cursor = new Carbon($start);
            $limit = new Carbon($end);

            while ($cursor < $limit) {
                $available = $this->isAvaliableInterval($date, $doctorId, $cursor);
                $slotStart = $cursor->format('g:i A');
                $cursor->addMinutes(30);

                $slots[] = [
                    'start' => $slotStart,
                    'end'   => $cursor->format('g:i A'),
                    'taken' => ! $available,
                ];

                if ($available) {
                    $free++;
                }
            }
        }

        return [
            'range' => $start && $end
                ? (new Carbon($start))->format('G:i').' – '.(new Carbon($end))->format('G:i')
                : null,
            'free'  => $free,
            'total' => count($slots),
            'slots' => $slots,
        ];
    }

	private function getIntervals($start, $end, $date, $doctorId){
		$start = new Carbon($start);
    	$end = new Carbon($end);

    	$intervals = [];
    	while($start < $end){
    		$interval = [];

    		$interval['start'] = $start->format('g:i A');

    		// no existe una cita para esta hora
    		$available = $this->isAvaliableInterval($date, $doctorId, $start);

    		$start->addMinutes(30);
    		$interval['end'] = $start->format('g:i A');

    		if($available) {
    			$intervals []= $interval;
    		}

		}

		return $intervals;
	}
}
