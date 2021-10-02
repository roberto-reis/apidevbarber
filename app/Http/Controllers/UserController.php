<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use App\Models\BarberService;
use App\Models\UserAppointment;
use App\Models\UserFavorite;
use finfo;
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

    public function getFavorites()
    {
        $response = ['error' => '', 'list' => []];

        $favorites = UserFavorite::where('id_user', auth('api')->id())->get();

        if($favorites) {
            foreach($favorites as $favorite) {
                $barber = Barber::find($favorite['id_barber']);
                $barber['avatar'] = url('media/avatars/'.$barber['avatar']);

                $response['list'][] = $barber;
            }
        }
        
        return response()->json($response);
    }

    public function getAppointments()
    {
        $response = ['error' => '', 'list' => []];

        $appointments = UserAppointment::select()
            ->where('id_user', auth('api')->id())
            ->orderBy('ap_datetime', 'DESC')
        ->get();

        if($appointments) {

            foreach($appointments as $appointment) {
                $barber = Barber::find($appointment['id_barber']);
                $barber['avatar'] = url('media/avatar/'.$barber['avatar']);

                $barberService = BarberService::find($appointment['id_service']);

                $response['list'][] = [
                    'id' => $appointment['id'],
                    'datetime' => $appointment['ap_datetime'],
                    'barber' => $barber,
                    'service' => $barberService
                ];
                
            }

        }

        return response()->json($response);
    }


}
