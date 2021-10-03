<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use App\Models\BarberPhoto;
use Illuminate\Http\Request;
use App\Models\BarberService;
use App\Models\BarberTestimonial;
use App\Models\BarberAvailability;
use App\Models\UserAppointment;
use App\Models\UserFavorite;

class BarberController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    private function searchGeo($address)
    {
        $address = urlencode($address);

        $key = env('MAPS_KEY', null);
        $url = 'https://maps.googleapis.com/maps/api/geocode/json/?address=' . $address . '&key=' . $key;

        // $response = Http::post($url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    
    /* public function createRandom()
    {
        $array = ['error' => ''];
        
        for ($q = 0; $q < 15; $q++) {
            $names = ['Boniek', 'Paulo', 'Pedro', 'Amanda', 'Leticia', 'Gabriel', 'Gabriela', 'Thais', 'Luiz', 'Diogo', 'José', 'Jeremias', 'Francisco', 'Dirce', 'Marcelo'];
            $lastnames = ['Santos', 'Silva', 'Santos', 'Silva', 'Alvaro', 'Sousa', 'Diniz', 'Josefa', 'Luiz', 'Diogo', 'Limoeiro', 'Santos', 'Limiro', 'Nazare', 'Mimoza'];
            $servicos = ['Corte', 'Pintura', 'Aparação', 'Unha', 'Progressiva', 'Limpeza de Pele', 'Corte Feminino'];
            $servicos2 = ['Cabelo', 'Unha', 'Pernas', 'Pernas', 'Progressiva', 'Limpeza de Pele', 'Corte Feminino'];
            $depos = [
                'Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptate consequatur tenetur facere voluptatibus iusto accusantium vero sunt, itaque nisi esse ad temporibus a rerum aperiam cum quaerat quae quasi unde.',
                'Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptate consequatur tenetur facere voluptatibus iusto accusantium vero sunt, itaque nisi esse ad temporibus a rerum aperiam cum quaerat quae quasi unde.',
                'Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptate consequatur tenetur facere voluptatibus iusto accusantium vero sunt, itaque nisi esse ad temporibus a rerum aperiam cum quaerat quae quasi unde.',
                'Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptate consequatur tenetur facere voluptatibus iusto accusantium vero sunt, itaque nisi esse ad temporibus a rerum aperiam cum quaerat quae quasi unde.',
                'Lorem ipsum dolor sit amet consectetur adipisicing elit. Voluptate consequatur tenetur facere voluptatibus iusto accusantium vero sunt, itaque nisi esse ad temporibus a rerum aperiam cum quaerat quae quasi unde.'
            ];
            $newBarber = new Barber();
            $newBarber->name = $names[rand(0, count($names) - 1)] . ' ' . $lastnames[rand(0, count($lastnames) - 1)];
            $newBarber->avatar = rand(1, 4) . '.png';
            $newBarber->stars = rand(2, 4) . '.' . rand(0, 9);
            $newBarber->latitude = '-23.5' . rand(0, 9) . '30907';
            $newBarber->longitude = '-46.6' . rand(0, 9) . '82759';
            $newBarber->save();
            $ns = rand(3, 6);
            for ($w = 0; $w < 4; $w++) {
                $newBarberPhoto = new BarberPhoto();
                $newBarberPhoto->id_barber = $newBarber->id;
                $newBarberPhoto->url = rand(1, 5) . '.png';
                $newBarberPhoto->save();
            }
            for ($w = 0; $w < $ns; $w++) {
                $newBarberService = new BarberService();
                $newBarberService->id_barber = $newBarber->id;
                $newBarberService->name = $servicos[rand(0, count($servicos) - 1)] . ' de ' . $servicos2[rand(0, count($servicos2) - 1)];
                $newBarberService->price = rand(1, 99) . '.' . rand(0, 100);
                $newBarberService->save();
            }
            for ($w = 0; $w < 3; $w++) {
                $newBarberTestimonial = new BarberTestimonial();
                $newBarberTestimonial->id_barber = $newBarber->id;
                $newBarberTestimonial->name = $names[rand(0, count($names) - 1)];
                $newBarberTestimonial->rate = rand(2, 4) . '.' . rand(0, 9);
                $newBarberTestimonial->body = $depos[rand(0, count($depos) - 1)];
                $newBarberTestimonial->save();
            }
            for ($e = 0; $e < 4; $e++) {
                $rAdd = rand(7, 10);
                $hours = [];
                for ($r = 0; $r < 8; $r++) {
                    $time = $r + $rAdd;
                    if ($time < 10) {
                        $time = '0' . $time;
                    }
                    $hours[] = $time . ':00';
                }
                $newBarberAvail = new BarberAvailability();
                $newBarberAvail->id_barber = $newBarber->id;
                $newBarberAvail->weekday = $e;
                $newBarberAvail->hours = implode(',', $hours);
                $newBarberAvail->save();
            }
        }
        return $array;
    } */
   

    public function list(Request $request)
    {
        $response = ['error' => ''];

        $latitude = $request->latitude;
        $longitude = $request->longitude;
        $city = $request->city;
        $offset = $request->offset;

        if (!$offset) {
            $offset = 0;
        }

        if (!empty($city)) {
            $retorno = $this->searchGeo($city);
            if (count($retorno['results']) > 0) {
                $latitude = $retorno['results'][0]['geometry']['location']['lat'];
                $longitude = $retorno['results'][0]['geometry']['location']['lng'];
            }
        } elseif (!empty($latitude) && !empty($longitude)) {

            $retorno = $this->searchGeo($latitude . ',' . $longitude);
            if (count($retorno['results']) > 0) {
                $city = $retorno['results'][0]['formatted_address'];
            }
        } else {
            $latitude = '-23.5630907';
            $longitude = '-46.6682795';
            $city = 'São Paulo';
        }

        $barbers = Barber::select(Barber::raw('*, SQRT(
            POW(69.1 * (latitude - ' . $latitude . '), 2) +
            POW(69.1 * (' . $longitude . ' - longitude) * COS(latitude / 57.3), 2)) AS distance'))
            ->havingRaw('distance < ?', [10])
            ->orderBy('distance', 'ASC')
            ->offset($offset)
            ->limit(5)
            ->get();

        foreach ($barbers as $barberKey => $barberValue) {
            $barbers[$barberKey]['avatar'] = url('media/avatars/' . $barbers[$barberKey]['avatar']);
        }
        $response['data'] = $barbers;
        $response['localidade'] = 'São Paulo';

        return response()->json($response);
    }

    public function one($id)
    {
        $response = ['error' => ''];

        $barber = Barber::find($id);
        if ($barber) {
            $barber['avatar'] = url('media/avatars/' . $barber['avatar']);
            $barber['favorited'] = false;
            $barber['photos'] = [];
            $barber['services'] = [];
            $barber['testimonials'] = [];
            $barber['available'] = [];

            // Verificando Favorito
            $cFavorito = UserFavorite::where('id_user', auth('api')->id())
                ->where('id_barber', $barber->id)
                ->count();
            if ($cFavorito > 0) {
                $barber['favorited'] = true;
            }

            //Pegando as fotos
            $barber['photos'] = BarberPhoto::select(['id', 'url'])
                ->where('id_barber', $barber->id)
                ->get();
            foreach ($barber['photos'] as $keyPhoto => $valuePhoto) {
                $barber['photos'][$keyPhoto]['url'] = url('media/uploads/' . $barber['photos'][$keyPhoto]['url']);
            }

            //Pegando os services do Barbeiro
            $barber['services'] = BarberService::select(['id', 'name', 'price'])
                ->where('id_barber', $barber->id)
                ->get();

            //Pegando os depoimentos do Barbeiro
            $barber['testimonials'] = BarberTestimonial::select(['id', 'name', 'rate', 'body'])
                ->where('id_barber', $barber->id)
                ->get();

            // Pegando a disponibilidade do barmeiro
            $availability = [];
            // Pegando a disponibilidade crua
            $avails = BarberAvailability::where('id_barber', $barber->id)->get();
            $availWeedays = [];
            foreach ($avails as $item) {
                $availWeedays[$item['weekday']] = explode(',', $item['hours']);
            }

            // Pegar os agendamentos dos próximos 20 dias
            $appointments = [];
            $appQuery = UserAppointment::where('id_barber', $barber->id)
                ->whereBetween('ap_datetime', [
                    date('Y-m-d') . ' 00:00:00',
                    date('Y-m-d', strtotime('+20 days')) . ' 23:59:59'
                ])
                ->get();

            foreach ($appQuery as $appItem) {
                $appointments[] = $appItem['ap_datetime'];
            }

            // Gera a disponibilidade real
            for ($q = 0; $q < 20; $q++) {
                $timeItem = strtotime('+' . $q . ' days');
                $weekday = date('w', $timeItem);

                if (in_array($weekday, array_keys($availWeedays))) {
                    $hours = [];

                    $dayItem = date('Y-m-d', $timeItem);
                    foreach ($availWeedays[$weekday] as $hoursItem) {
                        $dayFormated = $dayItem . ' ' . $hoursItem . ':00';
                        if (!in_array($dayFormated, $appointments)) {
                            $hours[] = $hoursItem;
                        }
                    }
                    if (count($hours) > 0) {
                        $availability[] = [
                            'date' => $dayItem,
                            'hours' => $hours
                        ];
                    }
                }
            }


            $barber['available'] = $availability;
            $response['data'] = $barber;
        } else {
            $response['error'] = 'Barbeiro não encontrado.';
        }

        return response()->json($response);
    }

    public function setAppointment(Request $request, $id)
    {
        $response = ['error' => ''];

        $service = $request->input('service');
        $year = intval($request->input('year'));
        $month = intval($request->input('month'));
        $day = intval($request->input('day'));
        $hour = intval($request->input('hour'));

        $month = ($month < 10) ? '0' . $month : $month;
        $day = ($day < 10) ? '0' . $day : $day;
        $hour = ($hour < 10) ? '0' . $hour : $hour;

        // 1. Verificar se o serviço existe
        $barberService = BarberService::select()
            ->where('id', $service)
            ->where('id_barber', $id)
            ->first();
        if ($barberService) {
            // 2. Verificar se a data é real
            $appointmentDate = $year . '-' . $month . '-' . $day . ' ' . $hour . ':00:00';

            if (strtotime($appointmentDate) > 0) {

                // 3. Verificar se o barbeiro já possui agendamento neste dia/hora
                $userAppointment = UserAppointment::select()
                    ->where('id_barber', $id)
                    ->where('ap_datetime', $appointmentDate)
                    ->count();

                if ($userAppointment === 0) {

                    // 4. Verificar se o barbeiro atende nesta data
                    $weekday = date('w', strtotime($appointmentDate));
                    $avail = BarberAvailability::select()
                        ->where('id_barber', $id)
                        ->where('weekday', $weekday)
                        ->first();
                    if ($avail) {
                        // 4.1 Verificar se o barbeiro atende nestas horas
                        $hours = explode(',', $avail['hours']);
                        if (in_array($hour . ':00', $hours)) {
                            // 5. Fazer o agendamento
                            $newUserAppointment = new UserAppointment();
                            $newUserAppointment->id_user = auth('api')->id();
                            $newUserAppointment->id_barber = $id;
                            $newUserAppointment->id_service = $service;
                            $newUserAppointment->ap_datetime = $appointmentDate;
                            $newUserAppointment->save();

                            $response['sucesso'] = 'Serviço agendado com sucesso.';
                        } else {
                            $response['error'] = 'Barbeiro não atende hora.';
                        }
                    } else {
                        $response['error'] = 'Barbeiro não atende neste dia.';
                    }
                } else {
                    $response['error'] = 'Barbeiro já possui agendamento neste dia/hora';
                }
            } else {
                $response['error'] = 'Data inválida!';
            }
        } else {
            $response['error'] = 'Serviço inexistente!';
        }

        return response()->json($response);
    }

    public function search(Request $request)
    {
        $response = ['error' => '', 'list' => []];

        $search = $request->input('search');

        if ($search) {

            $barbers = Barber::where('name', 'LIKE', '%' . $search . '%')->get();

            foreach ($barbers as $keyBarber => $barber) {
                $barbers[$keyBarber]['avatar'] = url('media/avatars/' . $barbers[$keyBarber]['avatar']);
            }

            $response['list'] = $barbers;
        } else {
            $response['error'] = 'Digite algo para buscar!';
        }

        return response()->json($response);
    }
}
