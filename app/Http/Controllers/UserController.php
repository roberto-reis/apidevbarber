<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use App\Models\UserFavorite;
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
        $data['avatar'] = url('media/avatars/' . $data['avatar']);
        $response['data'] = $data;

        return response()->json($response);
    }

    public function toggleFavorite(Request $request)
    {
        $response = ['error' => ''];

        $id_barber = $request->input('barber');
        $barber = Barber::find($id_barber);

        if ($barber) {
            $hasFavorite = UserFavorite::where('id_user', auth('api')->id())
                ->where('id_barber', $id_barber)
                ->first();

            if ($hasFavorite) {

                $hasFavorite->delete();
                $response['haveFavorite'] = false;

            } else {

                $newFavorite = new UserFavorite();
                $newFavorite->id_user = auth('api')->id();
                $newFavorite->id_barber = $id_barber;
                $newFavorite->save();
                $response['haveFavorite'] = true;

            }
        } else {
            $response['error'] = 'Barbeiro inexistente.';
        }

        return response()->json($response);
    }
}
