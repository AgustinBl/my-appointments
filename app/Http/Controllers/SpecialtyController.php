<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Specialty;

class SpecialtyController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}

    public function index()
    {
    	$specialties = Specialty::all();
    	return view('specialties.index', compact('specialties'));
    }

    public function create()
    {
    	return view('specialties.create');
    }

    private function performValidation(Request $request)
    {
        $rules = [
            'name' => 'required|min:3',
            'description' => 'required'
        ];
        $this->validate($request, $rules);
    }

    public function store(Request $request)
    {
    	//dd($request->all());
        $this->performValidation($request);
    	$specialty = new Specialty();
    	$specialty->name = $request->input('name');
    	$specialty->description = $request->input('description');
    	$specialty->save(); //INSER

        $notification = 'La especialidad se ha registrado correctamente.';
    	return redirect('/specialties')->with(compact('notification'));
    }

    public function edit(Specialty $specialty)
    {
    	return view('specialties.edit', compact('specialty'));
    }

    public function update(Request $request, Specialty $specialty)
    {
    	//dd($request->all());
        $this->performValidation($request);
    	$specialty->name = $request->input('name');
    	$specialty->description = $request->input('description');
    	$specialty->save(); // UPDATE

    	$notification = 'La especialidad se ha editado correctamente.';
        return redirect('/specialties')->with(compact('notification'));
    }

    public function destroy(Specialty $specialty)
    {
        $deletedName = $specialty->name;
        $specialty->delete();
        $notification = 'La especialidad '. $deletedName .' se ha eliminado correctamente.';
        return redirect('/specialties')->with(compact('notification'));
    }
}
