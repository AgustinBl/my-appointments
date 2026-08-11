<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\ValidateAndCreatePatient;

use Auth;
use App\Auth\JwtManager;
use App\User;

class AuthController extends Controller
{
	use ValidateAndCreatePatient;

	/** @var JwtManager */
	private $jwt;

	public function __construct(JwtManager $jwt)
	{
		$this->jwt = $jwt;
	}

    public function login(Request $request)
    {
    	$credentials = $request->only('email', 'password');

    	if (Auth::guard('api')->attempt($credentials)) {
		    $user = Auth::guard('api')->user();

		    if ($user->role !== 'patient') {
		    	Auth::guard('api')->logout();
		    	return $this->onlyPatientsResponse();
		    }

		    $jwt = $this->jwt->issue($user);
		    $success = true;

		    // Return successfull sign in response with the generated jwt.
		    return compact('success', 'user', 'jwt');
		} else {
		    // Return response for failed attempt.
			$success = false;
			$message = 'Invalid credentials';
			return compact('success', 'message');
		}
	}

	/**
	 * La app movil es el canal del paciente: el medico y el administrador
	 * trabajan desde el panel web, que es donde estan su agenda y los ABM.
	 *
	 * Se responde 422 y no 403 a proposito. El cliente movil traduce el 403 a un
	 * "error del servidor" generico, mientras que del 422 toma el primer mensaje
	 * de `errors` y lo muestra tal cual, que es lo que hace falta aca: si se
	 * devolviera el `success: false` del camino de credenciales invalidas, a un
	 * medico con la clave correcta le diria que sus credenciales son incorrectas.
	 */
	private function onlyPatientsResponse()
	{
		return response()->json([
			'message' => 'The given data was invalid.',
			'errors' => [
				'email' => ['Esta aplicación es solo para pacientes. Si sos profesional, ingresá desde el sitio web.'],
			],
		], 422);
	}

	public function logout()
	{
		Auth::guard('api')->logout();
		$success = true;
		return compact('success');
	}

	public function register(Request $request)
	{
		$this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        Auth::guard('api')->login($user);

        $jwt = $this->jwt->issue($user);
	    $success = true;
	    
	    return compact('success', 'user', 'jwt');
	}
}	
