<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Barber;
use App\Models\UserFavorite;
use Illuminate\Http\Request;
use App\Models\BarberService;
use App\Models\UserAppointment;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Validator;

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

        if ($favorites) {
            foreach ($favorites as $favorite) {
                $barber = Barber::find($favorite['id_barber']);
                $barber['avatar'] = url('media/avatars/' . $barber['avatar']);

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

        if ($appointments) {

            foreach ($appointments as $appointment) {
                $barber = Barber::find($appointment['id_barber']);
                $barber['avatar'] = url('media/avatar/' . $barber['avatar']);

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

    public function update(Request $request)
    {
        $response = ['error' => ''];

        $rules = [
            'name' => ['min:2'],
            'email' => ['email', Rule::unique('users')->ignore(auth('api')->id())],
            'password' => ['min:8', 'confirmed']
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $response['error'] = $validator->messages();

            return response()->json($response);
        }

        $user = User::find(auth('api')->id());
        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = password_hash($request->input('password'), PASSWORD_DEFAULT);
        $user->save();

        return response()->json($response);
    }

    public function updateAvatar(Request $request)
    {
        $response = ['error' => ''];

        $rules = [
            'avatar' => ['required', 'image', 'mimes:jpg,png,jpeg']
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $response['error'] = $validator->messages();
            return response()->json($response);
        }

        $avatar = $request->file('avatar');
        $path = public_path('media/avatars');
        $avatarName = md5(time().rand(0,999)).'.jpg';

        $img = Image::make($avatar->getRealPath());
        $img->fit(300, 300)->save($path.'/'.$avatarName);

        $user = User::find(auth('api')->id());
        $user->avatar = $avatarName;
        $user->save();

        return response()->json($response);
    }
}
