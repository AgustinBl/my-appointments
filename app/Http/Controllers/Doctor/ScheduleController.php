<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\WorkDay;
use Carbon\Carbon;

class ScheduleController extends Controller
{
	private $days = [
    		'Lunes', 'Martes', 'Miércoles','Jueves','Viernes','Sábado', 'Domingo'
    	];

    public function edit()
    {
    	$workDays = WorkDay::where('user_id', auth()->id())->orderBy('day')->get();

        if(count($workDays) > 0) {
            $workDays->map(function($workDay){
                $workDay->morning_start = (new Carbon($workDay->morning_start))
                    ->format('g:i A');
                $workDay->morning_end = (new Carbon($workDay->morning_end))
                    ->format('g:i A');
                $workDay->afternoon_start = (new Carbon($workDay->afternoon_start))
                    ->format('g:i A');
                $workDay->afternoon_end = (new Carbon($workDay->afternoon_end))
                    ->format('g:i A');
                return $workDay;
        });
        } else {
            $workDays = collect();
            for ($i=0; $i<7; ++$i)
                $workDays->push(new WorkDay());
        }

    	$days = $this->days;

        // Cuántas franjas de 30 minutos genera cada día. El mismo número
        // alimenta la columna, el gráfico y el total del encabezado.
        $slots = $workDays->map(function ($workDay) {
            return $workDay->active
                ? $this->countSlots($workDay->morning_start, $workDay->morning_end)
                  + $this->countSlots($workDay->afternoon_start, $workDay->afternoon_end)
                : 0;
        })->all();

        $totalSlots = array_sum($slots);

    	return view('schedule', compact('workDays','days','slots','totalSlots'));
    }

    public function store(Request $request)
    {
    	$active = $request->input('active') ?: [];
    	$morning_start = $request->input('morning_start');
    	$morning_end = $request->input('morning_end');
    	$afternoon_start = $request->input('afternoon_start');
    	$afternoon_end = $request->input('afternoon_end');

    	$errors = [];

    	for($i=0; $i<7; ++$i) {

    		if($morning_start[$i] > $morning_end[$i] ){
    			$errors []='Las horas del turno mañana son inconsistentes para el día ' . $this->days[$i] .'.';
    		}
    		if($afternoon_start[$i] > $afternoon_end[$i]){
    			$errors []='Las horas del turno tarde son inconsistentes para el día ' . $this->days[$i] .'.';
    		}

	    	WorkDay::updateOrCreate(
	    		[
	    			'day' => $i,
				    'user_id' => auth()->id(),
	    		],
	    		[
	    			// El control segmentado manda "1" o "0" por día.
				    'active' => isset($active[$i]) && $active[$i] == '1',

				    'morning_start' => $morning_start[$i],
				    'morning_end' =>$morning_end[$i],

				    'afternoon_start' =>$afternoon_start[$i],
				    'afternoon_end' =>$afternoon_end[$i]
	    		]
	    	);
	    }

	    if(count($errors) > 0)
	    	return back()->with(compact('errors'));

	    $notification = 'Guardamos tu horario. Los pacientes ya pueden reservar sobre estas ventanas.';
	    return back()->with(compact('notification'));
    }

    /** Franjas de 30 minutos que entran en una ventana. */
    private function countSlots($start, $end)
    {
        if (! $start || ! $end) {
            return 0;
        }

        $start = new Carbon($start);
        $end = new Carbon($end);

        return $start < $end ? (int) floor($start->diffInMinutes($end) / 30) : 0;
    }
}
