<?php namespace App\Interfaces;

use Carbon\Carbon;

interface ScheduleServiceInterface
{
	public function getAvaliableIntervals($date, $doctorId);
	public function isAvaliableInterval($date, $doctorId, Carbon $start);
}