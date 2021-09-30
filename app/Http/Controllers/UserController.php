<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function read()
    {
        $response = ['error' => ''];

        $data = Auth::user();
        $data['avatar'] = url('media/avatars/'.$data['avatar']);
        $response['data'] = $data;
        
        return response()->json($response);
    }
}
