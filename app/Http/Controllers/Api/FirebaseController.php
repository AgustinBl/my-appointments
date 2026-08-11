<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Auth;

class FirebaseController extends Controller
{
	public function postToken(Request $request)
	{
		$request->validate([
			'device_token' => 'required|string|max:255',
		]);

		$user = Auth::guard('api')->user();
		$deviceToken = $request->input('device_token');

		// El token identifica a la instalacion, no al usuario: si el mismo
		// telefono habia quedado asociado a otra cuenta, se lo sacamos para
		// que no reciba las notificaciones de quien ya no esta logueado.
		User::where('device_token', $deviceToken)
			->where('id', '!=', $user->id)
			->update(['device_token' => null]);

		$user->device_token = $deviceToken;
		$user->save();

		$success = true;
		return compact('success');
	}
}
