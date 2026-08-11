<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Specialty;
use App\WorkDay;

class SpecialtyController extends Controller
{
	public function index()
	{
		return Specialty::all(['id', 'name']);
	}

    public function doctors(Specialty $specialty)
    {
    	$doctors = $specialty->users()->get([
    		'users.id',
    		'users.name']);

        // Los días que atiende cada médico. La pantalla de reserva los muestra
        // junto al nombre para que el paciente no pruebe fechas a ciegas.
        $days = WorkDay::whereIn('user_id', $doctors->pluck('id'))
            ->where('active', true)
            ->orderBy('day')
            ->get(['user_id', 'day'])
            ->groupBy('user_id');

        return $doctors->map(function ($doctor) use ($days, $specialty) {
            $active = isset($days[$doctor->id]) ? $days[$doctor->id]->pluck('day')->all() : [];

            return [
                'id'        => $doctor->id,
                'name'      => $doctor->name,
                'specialty' => $specialty->name,
                'days'      => WorkDay::daysSentence($active),
            ];
        });
    }
}
