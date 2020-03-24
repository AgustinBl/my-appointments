<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FirebaseController extends Controller
{
	public function postToken(Request $request)
	{
		$user = Auth::guard('api')->user();
		
		if($request->has('device_token')) {
			$user->device_token = $request->input('device_token');
			$user->save();
		}
	}
}
