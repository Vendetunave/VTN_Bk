<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Users;
use App\Models\Noticias;
use App\Models\Servicios;
use App\Models\Dealerships;
use App\Models\DealershipsBrands;
use App\Models\ubicacion_ciudades;
use App\Models\imagenes;
use App\Models\Marcas;
use App\Models\tokens;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use Illuminate\Support\Facades\Mail;
use App\Models\Config;
use App\Models\DataSheet;
use App\Models\financiacion;
use App\Models\Pregunta;
use App\Models\Roles;
use App\Models\Vehicles;
use App\Models\TiposServicios;
use Illuminate\Support\Facades\Hash;
use File;
use ZipArchive;
use DateTime;


class OtrosController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function getServicios(Request $request)
    {
        $page = $request->query('page') ? $request->query('page') : 1;

        $servicio = $request->query('servicio') ? $request->query('servicio') : null;
        $ciudad = $request->query('ciudad') ? $request->query('ciudad') : null;

        $servicios = Servicios::select('servicios.*', 'TS.nombre AS servicio', 'UC.nombre AS labelCiudad')
            ->leftJoin('tipos_servicio AS TS', 'TS.id', 'servicios.tipo_servicio_id')
            ->join('ubicacion_ciudades AS UC', 'UC.id', 'servicios.ciudad_id');
        $total_all = $servicios->get();
        if ($servicio) {
            $servicios->where('TS.nombre', $servicio);
        }

        if ($ciudad) {
            $servicios->where('UC.nombre', $ciudad);
        }

        $total_records = count($servicios->get());
        $servicios = $servicios->offset(($page - 1) * 10)->limit(10)->get();

        $collection = collect($total_all);
        $filteredMarcas = $collection;

        $contadores = array(
            'servicios' => $filteredMarcas->countBy('servicio'),
            'ciudades' => $filteredMarcas->countBy('labelCiudad')
        );

        $result = [
            'pagina' => $page,
            'servicios' => $servicios,
            'contadores' => $contadores,
            'total_records' => $total_records
        ];

        return $result;
    }
    public function get_id_tipo($titulo)
    {
        switch ($titulo) {
            case 'NUEVO':
                return 1;
            case 'USADO':
                return 2;
        }
    }
    public function get_id_marca($titulo)
    {
        $marca = Marcas::where('nombre', $titulo)->first();
        return $marca->id;
    }
    public function concesionarios(Request $request)
    {
        $filtros = array(
            'ciudad' => $request->query('ciudad') ? $request->query('ciudad') : null,
            'tipo' => $request->query('tipo') ? $request->query('tipo') : null,
            'marca' => $request->query('marca') ? $request->query('marca') : null,
            'page' => $request->query('page') ? $request->query('page') : 1,
        );
        $servicios = Dealerships::select('dealerships.*', 'CD.nombre AS ciudadLabel')
            ->join('ubicacion_ciudades AS CD', 'CD.id', 'dealerships.city_id');

        if ($filtros['ciudad']) {
            $servicios->where('CD.nombre', $filtros['ciudad']);
        }
        if ($filtros['tipo']) {
            $servicios->where('dealerships.type_vehicle', $this->get_id_tipo($filtros['tipo']));
        }
        if ($filtros['marca']) {
            $servicios->join('dealerships_brands AS DB', 'DB.dealership_id', 'dealerships.id')
                ->where('DB.brand_id', $this->get_id_marca($filtros['marca']))
                ->groupBy('dealerships.id');
        }
        $servicios = $servicios->offset(($filtros['page'] - 1) * 10)->limit(10)->get();
        $tiposServicios = Marcas::where('categoria_id', 1)->orderBy('nombre')->get();
        $ciudades = ubicacion_ciudades::orderBy('nombre')->where('indicativo', 1)->get();

        $result = [
            'servicios' => $servicios,
            'tiposServicios' => $tiposServicios,
            'ciudades' => $ciudades,
            'filtros' => $filtros
        ];

        return $result;
    }
    public function financiacion(Request $request)
    {
        try {
            $finacicacion = financiacion::insert([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'fecha_nacimiento' => $request->fecha_nacimiento,
                'cuanto_cuesta' => str_replace('.', '', $request->cuanto_cuesta),
                'cuota_inicial' => str_replace('.', '', $request->cuota_inicial),
                'numero_cuotas' => $request->cuotas,
                'datacredito' => $request->datacredito,
                'rango_salarial' => $request->salario,
                'whatsapp' => $request->whatsapp,
                'email' => $request->email,
                'creado' => date('Y-m-d H:i:s')
            ]);

            $config = Config::select('correo_financiacion')->first();

            $subject = "Alguien está interesado en el servicio de financiación";
            $for = $config->correo_financiacion;

            Mail::send('mailFinanciacion', $request->all(), function ($msj) use ($subject, $for) {
                $msj->from("no-reply@vendetunave.co", "VendeTuNave");
                $msj->subject($subject);
                $msj->to($for);
            });

            $result = [
                'status' => true
            ];

            return $result;
        } catch (\Throwable $th) {
            $result = [
                'status' => false
            ];

            return $result;
        }
    }

    public function get_cities($id)
    {
        $ciudades = ubicacion_ciudades::orderBy('nombre')->where('id_departamento', $id)->get();

        $result = [
            'ciudades' => $ciudades
        ];

        return $result;
    }

    public function restablecer_contrasena_link(Request $request)
    {
        try {
            $user = Users::where('email', $request->email)->first();

            if ($user) {

                $linkEncrypt = md5(rand(1, 1000) . date('Y-m-d H:i:s'));

                tokens::insert([
                    'token' => $linkEncrypt,
                    'user_id' => $user->id,
                    'fecha' => date('Y-m-d H:i:s'),
                ]);

                $subject = "Restablecimiento de su contraseña";
                $for = $user->email;
                Mail::send('mailPassword', ['user' => $user, 'token' => $linkEncrypt], function ($msj) use ($subject, $for) {
                    $msj->from("no-reply@vendetunave.co", "VendeTuNave");
                    $msj->subject($subject);
                    $msj->to($for);
                });
                return ['status' => true];
            } else {
                return ['status' => false, 'message' => 'Lo sentimos, tu usuario no se encuentra en nuestra base de datos.'];
            }
        } catch (\Throwable $th) {
            return [
                'status' => false,
                'message' => 'Lo sentimos! Identificamos que tienes un problema con tu configuración de servidor de correos. Por favor ponte en contacto con tu administrador.',
                'error' => strval($th)
            ];
        }
    }

    public function validar_token(Request $request)
    {
        $tokenValid = tokens::where('token', $request->token)->first();

        if ($tokenValid) {
            if ($tokenValid->valido == 1) {
                return ['status' => true];
            } else {
                return ['status' => false];
            }
        } else {
            return ['status' => false];
        }
    }

    public function restablecer_contrasena(Request $request)
    {
        try {
            $token = tokens::where('token', $request->token)->first();

            \DB::table('users')->where('id', $token->user_id)
                ->update(['password' => Hash::make($request->pass), 'password_encrypt' => false]);

            \DB::table('tokens')->where('id', $token->id)->delete();

            $response = [
                'state' => true
            ];

            return $response;
        } catch (\Throwable $th) {
            $response = [
                'state' => false
            ];

            return $response;
        }
    }

    public function sitemap()
    {
        $vehiculos = Vehicles::select('id', 'title')->where('activo', 1)->get();
        $fichaTecnica = DataSheet::select('id', 'title')->where('active', 1)->get();
        $preguntas = Pregunta::select('id', 'titulo')->where('aprobado', 1)->get();

        $response = [
            'vehiculos' => $vehiculos,
            'ficha_tecnica' => $fichaTecnica,
            'preguntas' => $preguntas
        ];

        return $response;
    }

    public function form_contact(Request $request)
    {
        try {
            $arrayUrl = explode('-', $request->id);
            $id = $arrayUrl[COUNT($arrayUrl) - 1];

            $vehiculo = Vehicles::where('id', $id)->first();
            $user = Users::where('id', $vehiculo->vendedor_id)->first();

            $subject = "Alguien está interesado en tu publicación " . $vehiculo->title;
            $for = $user->email;
            Mail::send('mailContact', $request->all(), function ($msj) use ($subject, $for) {
                $msj->from("no-reply@vendetunave.co", "VendeTuNave");
                $msj->subject($subject);
                $msj->to($for);
            });
            return ['status' => true, 'message' => 'Le hemos notificado al vendedor que estas interesado en su vehículo.'];
        } catch (\Throwable $th) {
            return ['status' => false, 'message' => 'Lo sentimos! inténtalo más tarde.'];
        }
    }

    public function inAppBrowser(Request $request)
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($userAgent, 'Instagram')) {
            $file = rtrim(app()->basePath('public/' . 'FUNT.pdf'));
            header('Content-Description: File Transfer');
            header("Content-type: application/pdf");
            header('Content-Disposition: attachment; filename="' . basename('FUNT.pdf') . '"');
            header('Content-Length: ' . filesize('FUNT.pdf'));
            readfile($file);
        } else {
            header('Location: https://www.vendetunave.co/');
            exit();
        }
    }

    public function get_all_users(Request $request)
    {
        $users = User::select('users.id', 'users.nombre', 'email', 'R.nombre AS rol', 'activo', 'locked', 'confiable')
            ->join('roles AS R', 'R.id', 'rol_id')
            ->orderBy('users.id', 'ASC')
            ->get();


        $response = [
            'users' => $users,
        ];

        return $response;
    }

    public function get_by_user($id)
    {
        $users = User::select(
            'id',
            'nombre',
            'email',
            'rol_id',
            'activo',
            'locked',
            'telefono',
            'genero',
            'fecha_nacimiento',
            'image'
        )->where('id', $id)->first();
        $roles = Roles::all();

        $response = [
            'users' => $users,
            'roles' => $roles,
        ];

        return $response;
    }

    public function get_roles()
    {
        $roles = Roles::all();

        $response = [
            'roles' => $roles,
        ];

        return $response;
    }

    public function create_user(Request $request)
    {
        try {
            $userEmail = Users::where('email', $request->email)->first();
            if (empty($userEmail)) {
                \DB::table('users')->insert([
                    'nombre' => $request->nombre,
                    'email' => $request->email,
                    'telefono' => $request->telefono,
                    'genero' => $request->genero,
                    'fecha_nacimiento' => $request->fecha_nacimiento,
                    'rol_id' => $request->rol_id,
                    'password' => Hash::make($request->password),
                    'password_encrypt' => false,
                ]);
            }

            $response = [
                'status' => (empty($userEmail)) ? true : false,
                'message' => (empty($userEmail)) ? 'Datos creados correctamente' : 'Ya existe un usuario con ese correo'
            ];

            return $response;
        } catch (\Throwable $th) {
            $response = [
                'state' => false,
                'message' => $th
            ];
        }
    }

    public function updated_user(Request $request)
    {
        try {
            $userInfo = Users::where('id', $request->id)->first();
            $userEmail = [];
            if ($userInfo->email != $request->email) {
                $userEmail = Users::where('email', $request->email)->get();
            }

            \DB::table('users')->where('id', $request->id)
                ->update([
                    'nombre' => $request->nombre,
                    'email' => (COUNT($userEmail) > 0) ? $userInfo->email : $request->email,
                    'telefono' => $request->telefono,
                    'genero' => $request->genero,
                    'fecha_nacimiento' => $request->fecha_nacimiento,
                    'rol_id' => $request->rol_id,
                ]);

            $response = [
                'status' => true,
                'message' => (COUNT($userEmail) > 0) ? 'No se actualizo el email por ya se encuentra en uso' : 'Datos actualizado correctamente'
            ];

            return $response;
        } catch (\Throwable $th) {
            $response = [
                'state' => false,
                'message' => $th
            ];
        }
    }

    public function active_user(Request $request)
    {
        $user = Users::where('id', $request->id)->first();

        \DB::table('users')->where('id', $request->id)
            ->update(['activo' => ($user->activo) ? 0 : 1]);

        $result = [
            'status' => true,
            'active' => ($user->activo) ? false : true
        ];

        return $result;
    }

    public function bloqued_user(Request $request)
    {
        $user = Users::where('id', $request->id)->first();

        \DB::table('users')->where('id', $request->id)
            ->update(['locked' => ($user->locked) ? 0 : 1]);

        $result = [
            'status' => true,
            'locked' => ($user->locked) ? false : true
        ];

        return $result;
    }

    public function dependable_user(Request $request)
    {
        $user = Users::where('id', $request->id)->first();

        \DB::table('users')->where('id', $request->id)
            ->update(['confiable' => ($user->confiable) ? 0 : 1]);

        \DB::table('vehicles')->where('vendedor_id', $user->id)
            ->update(['confiable' => ($user->confiable) ? 0 : 1]);

        $result = [
            'status' => true,
            'dependable' => ($user->confiable) ? false : true
        ];

        return $result;
    }

    public function premium_user(Request $request)
    {
        $vehicle = Vehicles::where('id', $request->id)->first();

        \DB::table('vehicles')->where('id', $request->id)
            ->update(['premium' => ($vehicle->premium) ? 0 : 1, 'active_premium' => ($vehicle->premium) ? null : new DateTime()]);

        $result = [
            'status' => true,
            'premium' => ($vehicle->premium) ? false : true
        ];

        return $result;
    }

    public function get_all_news()
    {
        $news = Noticias::select('id', 'title', 'description')->get();

        $response = [
            'news' => $news,
        ];

        return $response;
    }

    public function delete_news(Request $request)
    {
        try {
            \DB::table('noticias')->where('id', $request->id)->delete();
            $news = Noticias::select('id', 'title', 'description')->get();

            $response = [
                'status' => true,
                'news' => $news,
            ];

            return $response;
        } catch (\Throwable $th) {
            $response = [
                'status' => false,
                'message' => strval($th)
            ];

            return $response;
        }
    }

    public function update_news(Request $request)
    {
        try {
            \DB::table('noticias')->where('id', $request->id)->update([
                'title' => $request->title,
                'description' => $request->description,
                'link' => $request->link,
            ]);

            $response = [
                'status' => true,
                'message' => 'Datos actualizados correctamente!'
            ];

            return $response;
        } catch (\Throwable $th) {
            $response = [
                'status' => false,
                'message' => strval($th)
            ];

            return $response;
        }
    }

    public function create_news(Request $request)
    {
        try {
            Noticias::insert([
                'title' => $request->title,
                'description' => $request->description,
                'link' => $request->link,
            ]);

            $response = [
                'status' => true,
                'message' => 'Datos creados correctamente!'
            ];

            return $response;
        } catch (\Throwable $th) {
            $response = [
                'status' => false,
                'message' => strval($th)
            ];

            return $response;
        }
    }

    public function get_by_news(Request $request)
    {
        $news = Noticias::select('id', 'title', 'description', 'link')->where('id', $request->id)->first();

        $response = [
            'news' => $news,
        ];

        return $response;
    }

    public function get_all_services()
    {
        $services = Servicios::select('servicios.id', 'servicios.nombre', 'direccion', 'telefono', 'TS.nombre AS servicio')
            ->join('tipos_servicio AS TS', 'TS.id', 'servicios.tipo_servicio_id')
            ->orderBy('id', 'DESC')
            ->get();

        $response = [
            'services' => $services,
        ];

        return $response;
    }

    public function get_by_services(Request $request)
    {
        $services = Servicios::where('id', $request->id)->first();
        $tiposServicios = TiposServicios::all();
        $ciudades = ubicacion_ciudades::orderBy('nombre')->where('indicativo', 1)->get();

        $response = [
            'services' => $services,
            'tipos_servicios' => $tiposServicios,
            'ciudades' => $ciudades
        ];
        return $response;
    }

    public function create_services(Request $request)
    {
        try {
            if ($request->hasFile('image1')) {
                $file = $request->file('image1');
                $extension = 'webp';
                $name = uniqid();
                $filenametostore = $name . '.' . $extension;

                $imageConvert = (string) Image::make($file)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/servicios-productos/' . $name . '.webp', $imageConvert, 'public');
                Servicios::insert([
                    'nombre' => $request->title,
                    'descripcion' => $request->description,
                    'ciudad_id' => $request->city,
                    'direccion' => $request->address,
                    'tipo_servicio_id' => $request->type,
                    'url' => $request->link,
                    'telefono' => $request->phone,
                    'image' => 'https://vendetunave.s3.amazonaws.com/vendetunave/images/servicios-productos/' . $filenametostore
                ]);

                $response = [
                    'status' => true,
                    'message' => 'Datos creados correctamente!'
                ];
            } else {
                $response = [
                    'status' => false,
                    'message' => 'Imagen invalida!'
                ];
            }

            return $response;
        } catch (\Throwable $th) {
            $response = [
                'status' => false,
                'message' => strval($th)
            ];

            return $response;
        }
    }

    public function update_services(Request $request)
    {
        try {
            \DB::table('servicios')->where('id', $request->id)->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'ciudad_id' => $request->ciudad_id,
                'direccion' => $request->direccion,
                'tipo_servicio_id' => $request->tipo_servicio_id,
                'url' => $request->url,
                'telefono' => $request->telefono,
            ]);

            if ($request->hasFile('image1')) {
                $file = $request->file('image1');
                $extension = 'webp';
                $name = uniqid();
                $filenametostore = $name . '.' . $extension;

                $imageConvert = (string) Image::make($file)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/servicios-productos/' . $name . '.webp', $imageConvert, 'public');
                \DB::table('servicios')->where('id', $request->id)->update([
                    'image' => 'https://vendetunave.s3.amazonaws.com/vendetunave/images/servicios-productos/' . $filenametostore
                ]);
            }

            $response = [
                'status' => true,
                'message' => 'Datos creados correctamente!'
            ];

            return $response;
        } catch (\Throwable $th) {
            $response = [
                'status' => false,
                'message' => strval($th)
            ];

            return $response;
        }
    }

    public function delete_services(Request $request)
    {
        Servicios::where('id', $request->id)->delete();
        $services = Servicios::select('servicios.id', 'servicios.nombre', 'direccion', 'telefono', 'TS.nombre AS servicio')
            ->join('tipos_servicio AS TS', 'TS.id', 'servicios.tipo_servicio_id')
            ->orderBy('id', 'DESC')
            ->get();

        $response = [
            'status' => true,
            'message' => 'Se elimino la servicio!',
            'services' => $services,

        ];
        return $response;
    }

    public function get_all_dealerships()
    {
        $dealerships = Dealerships::select('id', 'name', 'address', 'phone', \DB::raw('IF(type_vehicle = 1, "Nuevo" , "Usado") AS type'))
            ->orderBy('id', 'DESC')
            ->get();

        $response = [
            'dealerships' => $dealerships,
        ];

        return $response;
    }

    public function get_by_dealerships(Request $request)
    {
        $dealerships = Dealerships::select('dealerships.*', \DB::raw('IF(type_vehicle = 1, "Nuevo" , "Usado") AS type'))
            ->where('id', $request->id)
            ->first();

        $dealershipsBrands = DealershipsBrands::select('M.nombre')
            ->join('marcas AS M', 'M.id', 'dealerships_brands.brand_id')
            ->where('dealership_id', $request->id)
            ->get();
        $arrayDealerships = [];
        foreach ($dealershipsBrands as $value) {
            array_push($arrayDealerships, $value->nombre);
        }
        $marcas = Marcas::select('marcas.id', 'marcas.nombre', 'TV.nombre AS nombrePadre')
            ->join('tipo_vehiculos AS TV', 'TV.id', 'marcas.categoria_id')
            ->get();
        $ciudades = ubicacion_ciudades::orderBy('nombre')->where('indicativo', 1)->get();

        $response = [
            'dealerships' => $dealerships,
            'dealerships_brands' => $arrayDealerships,
            'marcas' => $marcas,
            'ciudades' => $ciudades
        ];

        return $response;
    }

    public function create_dealerships(Request $request)
    {
        try {
            if ($request->hasFile('image1')) {
                $file = $request->file('image1');
                $extension = 'webp';
                $name = uniqid();
                $filenametostore = $name . '.' . $extension;

                $imageConvert = (string) Image::make($file)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/dealerships/' . $name . '.webp', $imageConvert, 'public');

                $dealerships = Dealerships::insertGetId([
                    'name' => $request->title,
                    'description' => $request->description,
                    'city_id' => $request->city,
                    'address' => $request->address,
                    'type_vehicle' => $request->type,
                    'latitude' => $request->lat,
                    'longitude' => $request->lon,
                    'phone' => $request->phone,
                    'image' => 'https://vendetunave.s3.amazonaws.com/vendetunave/images/dealerships/' . $filenametostore
                ]);

                if ($request->marks) {
                    $marcas = explode(',', $request->marks);

                    foreach ($marcas as $marca) {
                        $marcaQuery = Marcas::where('nombre', $marca)->first();

                        DealershipsBrands::insert([
                            'dealership_id' => $dealerships,
                            'brand_id' => $marcaQuery->id,
                        ]);
                    }
                }

                $response = [
                    'status' => true,
                    'message' => 'Datos creados correctamente!'
                ];
            } else {
                $response = [
                    'status' => false,
                    'message' => 'Imagen invalida!'
                ];
            }

            return $response;
        } catch (\Throwable $th) {
            $response = [
                'status' => false,
                'message' => $th
            ];
        }
    }

    public function update_dealerships(Request $request)
    {
        try {
            $dealerships = \DB::table('dealerships')->where('id', $request->id)->update([
                'name' => $request->name,
                'description' => $request->description,
                'city_id' => $request->city_id,
                'address' => $request->address,
                'type_vehicle' => $request->type_vehicle,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'phone' => $request->phone,
            ]);

            if ($request->marks) {
                DealershipsBrands::where('dealership_id', $request->id)->delete();
                $marcas = explode(',', $request->marks);

                foreach ($marcas as $marca) {
                    $marcaQuery = Marcas::where('nombre', $marca)->first();

                    DealershipsBrands::insert([
                        'dealership_id' => $request->id,
                        'brand_id' => $marcaQuery->id,
                    ]);
                }
            }

            if ($request->hasFile('image1')) {
                $file = $request->file('image1');
                $extension = 'webp';
                $name = uniqid();
                $filenametostore = $name . '.' . $extension;

                $imageConvert = (string) Image::make($file)->encode('webp', 100);
                Storage::disk('s3')->put('vendetunave/images/dealerships/' . $name . '.webp', $imageConvert, 'public');

                $dealerships = \DB::table('dealerships')->where('id', $request->id)->update([
                    'image' => 'https://vendetunave.s3.amazonaws.com/vendetunave/images/dealerships/' . $filenametostore
                ]);
            }

            $response = [
                'status' => true,
                'message' => 'Datos creados correctamente!'
            ];

            return $response;
        } catch (\Throwable $th) {
            $response = [
                'status' => false,
                'message' => $th
            ];
        }
    }

    public function delete_dealerships(Request $request)
    {
        Dealerships::where('id', $request->id)->delete();
        DealershipsBrands::where('dealership_id', $request->id)->delete();
        $dealerships = Dealerships::select('id', 'name', 'address', 'phone', \DB::raw('IF(type_vehicle = 1, "Nuevo" , "Usado") AS type'))
            ->orderBy('id', 'DESC')
            ->get();

        $response = [
            'status' => true,
            'dealerships' => $dealerships,
            'message' => 'Se elimino la concesionario!'
        ];
        return $response;
    }

    public function downloadZip(Request $request)
    {
        try {
            $imagenes = imagenes::select(
                \DB::raw('CONCAT("https://vendetunave.s3.amazonaws.com/", imagenes.path, imagenes.nombre, ".", imagenes.extension) AS url'),
            )
                ->join('imagenes_vehiculo AS IV', 'IV.id_image', 'imagenes.id')
                ->where('IV.id_vehicle', $request->id)
                ->orderBy('imagenes.order', 'ASC')
                ->get();

            $name =  uniqid();
            $filePath = app()->basePath('public/' . $name . '.zip');
            $zip = new \ZipArchive();

            if ($zip->open($filePath, \ZipArchive::CREATE) !== true) {
                throw new \RuntimeException('Cannot open ' . $filePath);
            }

            foreach ($imagenes as $image) {
                $download_file = file_get_contents($image->url);
                $zip->addFromString(basename($image->url), $download_file);
            }
            $zip->close();

            $response = [
                'status' => true,
                'path' => $name . '.zip'
            ];

            return $response;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function removeZip(Request $request)
    {
        unlink(app()->basePath('public/' . $request->path));
    }
}
