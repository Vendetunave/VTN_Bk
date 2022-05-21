<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Users;
use App\Models\Busquedas;
use App\Models\Pregunta;
use App\Models\Respuestas;
use App\Models\Favoritos;
use App\Models\FavoritesDataSheets;
use App\Models\Vehicles;

use App\Models\TipoVehiculos;
use App\Models\Combustibles;
use App\Models\Colores;
use App\Models\Transmisiones;
use App\Models\TipoPrecio;
use App\Models\TipoMoto;
use App\Models\ubicacion_departamentos;
use App\Models\tipo_accesorio;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class UsuarioController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function profile($id)
    {
        $result = Users::select('nombre', 'telefono', 'email', 'genero', 'fecha_nacimiento', 'image', 'facebook', 'instagram', 'tiktok', 'website')
            ->where('id', $id)
            ->first();
        $result->status = true;
        return $result;
    }
    public function busquedas(Request $request, $id)
    {

        $page = $request->query('page') ? $request->query('page') : 1;
        $busquedasTotal = Busquedas::leftJoin('vehicles AS V', 'V.id', 'busquedas.vehiculo_id')
            ->where('user_id', $id)
            ->where('V.activo', 1)
            ->count();

        $busquedas = Busquedas::select('busquedas.id', 'busquedas.vehiculo_id', 'busquedas.fecha', 'V.title', 'I.nombre AS nameImage', 'I.extension', 'UC.nombre AS labelCiudad')
            ->leftJoin('vehicles AS V', 'V.id', 'busquedas.vehiculo_id')
            ->leftJoin('ubicacion_ciudades AS UC', 'UC.id', 'V.ciudad_id')
            ->leftJoin('imagenes_vehiculo AS IV', 'IV.id_vehicle', 'V.id')
            ->leftJoin('imagenes AS I', 'I.id', 'IV.id_image')
            ->where('user_id', $id)
            ->where('V.activo', 1)
            ->groupBy('V.id')
            ->orderBy('busquedas.id', 'DESC')
            ->offset(($page - 1) * 10)->limit(10)->get();

        $result = [
            'busquedas' => $busquedas,
            'busquedasTotal' => $busquedasTotal,
            'page' => $page * 1,
        ];

        return $result;
    }
    public function favoritos(Request $request, $id)
    {

        $vehiculos = Favoritos::select('favoritos.id', 'favoritos.vehiculo_id', 'V.title', 'V.precio', 'I.nombre AS nameImage', 'I.extension', 'UC.nombre AS labelCiudad')
            ->join('vehicles AS V', 'V.id', 'favoritos.vehiculo_id')
            ->join('ubicacion_ciudades AS UC', 'UC.id', 'V.ciudad_id')
            ->join('imagenes_vehiculo AS IV', 'IV.id_vehicle', 'V.id')
            ->join('imagenes AS I', 'I.id', 'IV.id_image')
            ->where('user_id', $id)
            ->where('V.activo', 1)
            ->groupBy('V.id')
            ->orderBy('favoritos.id', 'DESC')
            ->get();

        $fichas_tecnicas = FavoritesDataSheets::select()
            ->join('data_sheet AS DS', 'DS.id', 'favorites_data_sheet.datasheet_id')
            ->join('images_data_sheet AS I', 'I.datasheet_id', \DB::raw('DS.id AND I.order = 1'))
            ->where('favorites_data_sheet.user_id', $id)
            ->where('DS.active', 1)
            ->orderBy('favorites_data_sheet.id', 'DESC')
            ->get();

        $result = [
            'vehiculos' => $vehiculos,
            'fichas_tecnicas' => $fichas_tecnicas
        ];
        return $result;
    }
    public function publicaciones(Request $request, $id)
    {
        /****/

        $filtros = array(
            'page' => $request->query('page') ? $request->query('page') : 1,
            'q' => $request->query('q') ? $request->query('q') : null
        );

        $vehicles = Vehicles::select(
            'vehicles.id',
            'vehicles.title',
            'vehicles.sku',
            'vehicles.ano',
            'vehicles.precio',
            'vehicles.fecha_creacion',
            'vehicles.activo',
            'vehicles.vendido',
            'UC.nombre AS labelCiudad',
            'I.nombre AS nameImage',
            'I.extension',
            'M.nombre AS modeloLabel'
        )
            ->join('ubicacion_ciudades AS UC', 'UC.id', 'vehicles.ciudad_id')
            ->join('imagenes_vehiculo AS IV', 'IV.id_vehicle', 'vehicles.id')
            ->join('imagenes AS I', 'I.id', 'IV.id_image')
            ->join('modelos AS M', 'M.id', 'vehicles.modelo_id')
            ->where('vehicles.vendedor_id', $id);
            if ($filtros['q']) {
                $vehicles = $vehicles->where('vehicles.title', 'LIKE', '%' . rtrim(ltrim($filtros['q'])) . '%');
            }
            $vehicles = $vehicles->orderBy('vehicles.fecha_creacion', 'DESC')
            ->groupBy('vehicles.id');
        $total_records = count($vehicles->get());
        if ($request->query('page', null)) {
            $vehicles = $vehicles->offset(($filtros['page'] - 1) * 20)->limit(20)->get();
        } else {
            $vehicles = $vehicles->get();
        }

        // $accesorios = Accesorios::select(
        //     'accesorios.id',
        //     'accesorios.title',
        //     'accesorios.precio',
        //     'accesorios.fecha_creacion',
        //     'accesorios.activo',
        //     'accesorios.vendido',
        //     'UC.nombre AS labelCiudad',
        //     'I.nombre AS nameImage',
        //     'I.extension'
        // )
        //     ->leftJoin('ubicacion_ciudades AS UC', 'UC.id', 'accesorios.ciudad_id')
        //     ->leftJoin('imagenes_accesorios AS IV', 'IV.accesorio_id', 'accesorios.id')
        //     ->leftJoin('imagenes AS I', 'I.id', 'IV.image_id')
        //     ->where('vendedor_id', $id)
        //     ->where('accesorios.vendido', 0)
        //     ->orderBy('accesorios.fecha_creacion', 'DESC')
        //     ->orderBy('accesorios.id', 'DESC')
        //     ->groupBy('accesorios.id')
        //     ->get();

        $result = [
            'total_records' => $total_records,
            'vehiculos' => $vehicles,
            'filtros' => $filtros
            // 'accesorios' => $accesorios
        ];
        return $result;
    }
    public function form_producto(Request $request)
    {

        $categories = TipoVehiculos::all();
        $tipoAccesorio = tipo_accesorio::all();
        $combustibles = Combustibles::all();
        $colores = Colores::all();
        $transmisiones = Transmisiones::all();
        $tipoPrecio = TipoPrecio::all();
        $departamentos = ubicacion_departamentos::select('*')->orderBy('nombre')->get();
        $tipoMoto = TipoMoto::all();

        $result = [
            'categories' => $categories,
            'combustibles' => $combustibles,
            'colores' => $colores,
            'transmisiones' => $transmisiones,
            'tipoPrecio' => $tipoPrecio,
            'departamentos' => $departamentos,
            'tipoAccesorio' => $tipoAccesorio,
            'tipoMoto' => $tipoMoto,
        ];
        return $result;
    }
    public function make_comment(Request $request)
    {
        $user = Users::where('id', $request->id)->first();
        if ($user->locked == 0) {

            $pregunta = Pregunta::select('U.id', 'U.nombre', 'U.email', 'pregunta.titulo')
                ->join('users AS U', 'U.id', 'pregunta.user_id')
                ->where('pregunta.id', $request->idPregunta)
                ->first();
            
            $usersQuestion = Respuestas::select('U.id', 'U.nombre', 'U.email')
                ->join('users AS U', 'U.id', 'respuestas.user_id')
                ->where('pregunta_id', $request->idPregunta)
                ->where('user_id', '<>', $request->user_id)
                ->where('user_id', '<>', $pregunta->id)
                ->where('user_id', '<>', 3)
                ->groupBy('user_id')
                ->get();

            Respuestas::insert(['pregunta_id' => $request->idPregunta, 'respuesta' => $request->comentario, 'user_id' => $request->id, 'fecha' => date('Y-m-d H:i:s')]);

            $slug = str_replace(' ', '-', $pregunta->titulo);
            $slug = str_replace('?', '', $slug);
            $slug = str_replace('¿', '', $slug);
            $slug = $slug . '-' . $request->idPregunta;

            $subject = "Comunidad";
            if ($request->id !== $pregunta->id) {
                $forQuestion = $pregunta->email;
                Mail::send('mailCommunity', ['nombre' => $pregunta->nombre, 'slug' => $slug], function ($msj) use ($subject, $forQuestion) {
                    $msj->from("no-reply@vendetunave.co", "VendeTuNave");
                    $msj->subject($subject);
                    $msj->to($forQuestion);
                });
            }
            
            foreach ($usersQuestion as $item) {
                $for = $item->email;
                Mail::send('mailCommunity', ['nombre' => $item->nombre, 'slug' => $slug], function ($msj) use ($subject, $for) {
                    $msj->from("no-reply@vendetunave.co", "VendeTuNave");
                    $msj->subject($subject);
                    $msj->to($for);
                });
            }
        }

        $result = [
            'state' => ($user->locked == 0) ? true : false
        ];
        return $result;
    }
    public function make_favorito(Request $request)
    {

        FavoritesDataSheets::where('datasheet_id', $request->idVehicle)->where('user_id', $request->idUser)->delete();
        $favorito = FavoritesDataSheets::insert([
            'datasheet_id' => $request->idVehicle,
            'user_id' => $request->idUser
        ]);

        $result = [
            'state' => true,
            'message' => 'Se agrego a favoritos'
        ];
        return $result;
    }
    public function make_favorito_vehiculo(Request $request)
    {
        $accion = 0;
        if ($request->state) {
            $favorito = Favoritos::insert([
                'vehiculo_id' => $request->idVehicle,
                'user_id' => $request->idUser
            ]);
            $accion = 1;
        } else {
            $favorito = Favoritos::where('vehiculo_id', $request->idVehicle)->where('user_id', $request->idUser)->delete();
            $accion = 2;
        }
        if ($favorito) {
            $result = [
                'state' => true,
                'message' => ($accion === 1) ? 'Se agrego a favoritos' : 'Se elimino de favoritos'
            ];
        } else {
            $result = [
                'state' => true,
            ];
        }
        return $result;
    }
    public function remove_busqueda(Request $request)
    {
        $favorito = Busquedas::where('vehiculo_id', $request->vehicle_id)->where('user_id', $request->user_id)->delete();
        $result = [
            'state' => true,
        ];
        return $result;
    }
    public function remove_favorito_vehiculo(Request $request)
    {
        $favorito = Favoritos::where('vehiculo_id', $request->vehicle_id)->where('user_id', $request->user_id)->delete();
        $result = [
            'state' => true,
        ];
        return $result;
    }
    public function remove_favorito_ficha(Request $request)
    {
        $favorito = FavoritesDataSheets::where('datasheet_id', $request->ficha_id)->where('user_id', $request->user_id)->delete();
        $result = [
            'state' => true,
        ];
        return $result;
    }
    public function profile_update(Request $request)
    {
        $userInfo = Users::where('id', $request->user_id)->first();
        $userEmail = [];
        if ($userInfo->email != $request->email) {
            $userEmail = Users::where('email', $request->email)->get();
        }

        if ($request->old_password != null) {

            $responseFailPassword = [
                'state' => false,
                'message' => 'La contraseña actual no coincide con tu contraseña'
            ];
            if ($userInfo->password_encrypt) {
                if (md5($request->old_password) !== $userInfo->password) {
                    return $responseFailPassword;
                }
            } else {
                $credentials = array(
                    "email" => $userInfo->email,
                    "password" => $request->old_password
                );
                if (!$token = Auth::attempt($credentials)) {
                    return $responseFailPassword;
                }
            }
        }

        if ($request->old_password == null) {
            $user = \DB::table('users')->where('id', $request->user_id)
                ->update([
                    'nombre' => $request->nombre,
                    'telefono' => $request->telefono,
                    'genero' => $request->genero,
                    'fecha_nacimiento' => $request->fecha_nacimiento,
                    'website' => $request->get('website', ''),
                    'facebook' => $request->get('facebook', ''),
                    'instagram' => $request->get('instagram', ''),
                    'tiktok' => $request->get('tiktok', '')
                ]);
        } else {
            $user = \DB::table('users')->where('id', $request->user_id)
                ->update([
                    'nombre' => $request->nombre,
                    'telefono' => $request->telefono,
                    'genero' => $request->genero,
                    'fecha_nacimiento' => $request->fecha_nacimiento,
                    'website' => $request->get('website', ''),
                    'facebook' => $request->get('facebook', ''),
                    'instagram' => $request->get('instagram', ''),
                    'tiktok' => $request->get('tiktok', ''),
                    'password' => Hash::make($request->new_password),
                    'password_encrypt' => false
                ]);
        }

        if ($request->hasFile('file') && $request->cambiarImage == 1) {
            $filenamewithextension = $request->file('file')->getClientOriginalName();
            $filename = pathinfo($filenamewithextension, PATHINFO_FILENAME);
            $extension = $request->file('file')->getClientOriginalExtension();
            $name = Str::random(4) . '_' . time();
            $filenametostore = $name . '.' . $extension;

            \Storage::disk('s3')->put('vendetunave/images/usuarios/' . $filenametostore, fopen($request->file('file'), 'r+'), 'public');

            $user = \DB::table('users')->where('id', $request->user_id)
                ->update([
                    'image' => $filenametostore,
                ]);
        }

        $response = [
            'state' => (COUNT($userEmail) > 0) ? false : true,
            'message' => (COUNT($userEmail) > 0) ? 'No se actualizo el email por ya se encuentra en uso' : 'Datos actualizado correctamente'
        ];

        return $response;
    }
}
