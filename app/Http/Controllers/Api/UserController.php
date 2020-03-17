<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;
use JwtAuth;

class UserController extends Controller
{
    public function show()
    {
    	return Auth::guard('api')->user();
    }
}
