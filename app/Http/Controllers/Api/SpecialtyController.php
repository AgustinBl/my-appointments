<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Specialty;

class SpecialtyController extends Controller
{
	public function index()
	{
		return Specialty::all(['id', 'name']);
	}

    public function doctors(Specialty $specialty)
    {
    	return $specialty->users()->get([
    		'users.id',
    		'users.name']);
    }
}
