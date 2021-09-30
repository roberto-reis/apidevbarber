<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'create', 'anauthorized']]);
    }

    public function login(Request $request)
    {
        $response = ['error' => ''];

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['error' => 'Não autenticado'], 401);
        }

        $data = auth('api')->user();
        $data['avatar'] = url('media/avatars/'.$data['avatar']);
        $response['data'] = $data;
        $response['token'] = $token;

        return response()->json($response);
    }


    public function create(Request $request)
    {
        $response = ['error' => ''];

        $request->validate([
            'name' => ['required'],
            'email' => ['required', 'unique:users'],
            'password' => ['required', 'min:8']
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'avatar' => $request->avatar,
                'password' => Hash::make($request->password)
            ]);

            // Auth user
            $token = auth('api')->login($user);

            $response['token'] = $token;
            $response['data'] = $user;
        } catch (Exception $error) {
            $response['error'] = ['Error ao salvar user' . $error->getMessage()];
        }

        return response()->json($response);
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        $response = ['error' => ''];

        $data = auth('api')->user();
        $data['avatar'] = url('media/avatars/'.$data['avatar']);
        $response['data'] = $data;
        $response['token'] = auth('api')->refresh();

        return response()->json($response);

    }

    public function anauthorized()
    {
        return response()->json(['error'=>'Não Autorizado.'], 401);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}
