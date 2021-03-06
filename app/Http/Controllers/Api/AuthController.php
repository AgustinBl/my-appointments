<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\ValidateAndCreatePatient;

use Auth;
use JwtAuth;
use App\User;

class AuthController extends Controller
{
	use ValidateAndCreatePatient;

    public function login(Request $request)
    {
    	$credentials = $request->only('email', 'password');
    	
    	if (Auth::guard('api')->attempt($credentials)) {
		    $user = Auth::guard('api')->user();
		    $jwt = JwtAuth::generateToken($user);
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

	public function logout()
	{
		//$user = Auth::guard('api')->user();
		//$user->device_token = null;
		//$user->save();
		//JwtAuth::clearCache($user);
		Auth::guard('api')->logout();
		$success = true;
		return compact('success');
	}

	public function register(Request $request)
	{
		$this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        Auth::guard('api')->login($user);

        $jwt = JwtAuth::generateToken($user);
	    $success = true;
	    
	    return compact('success', 'user', 'jwt');
	}
}	
