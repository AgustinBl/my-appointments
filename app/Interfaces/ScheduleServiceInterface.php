<?php namespace App\Interfaces;

use App\WorkDay;
use Carbon\Carbon;

interface ScheduleServiceInterface
{
	public function getAvaliableIntervals($date, $doctorId);
	public function isAvaliableInterval($date, $doctorId, Carbon $start);

	// Franjas del día con las tomadas incluidas, para poder mostrarlas tachadas.
	public function getDayIntervals($date, $doctorId);

	// Días de la semana en que el médico atiende (0 = lunes).
	public function activeDays($doctorId);

	// Cantidad de franjas de 30 minutos que genera un día cargado.
	public function slotsInDay(WorkDay $workDay);
}
