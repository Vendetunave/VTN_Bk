<?php

namespace App\Http\Controllers;

use App\Models\Modelos;
use App\Models\Marcas;
use App\Models\Config;
use App\Models\Permissions;
use App\Models\Roles;
use App\Models\ubicacion_ciudades;
use App\Models\TiposServicios;
use App\Models\TipoVehiculos;
use App\Models\Modules;
use App\Models\PermissionsUsers;

use Illuminate\Http\Request;

class ConfiguracionesController extends Controller
{
    public function configs()
    {
        $tiposServicios = TiposServicios::all();
        $categorias = TipoVehiculos::all();
        $configuraciones = Config::first();
        $marcas = Marcas::select('marcas.id', 'marcas.nombre', 'TV.nombre AS nombrePadre')
            ->join('tipo_vehiculos AS TV', 'TV.id', 'marcas.categoria_id')
            ->get();
        $modelos = Modelos::select('modelos.id', 'modelos.nombre', 'M.nombre AS nombrePadre')
            ->join('marcas AS M', 'M.id', 'modelos.marca_id')
            ->get();
        $ciudades = ubicacion_ciudades::orderBy('nombre')->where('indicativo', 1)->get();

        $response = [
            'configuraciones' => $configuraciones,
            'categorias' => $categorias,
            'ciudades' => $ciudades,
            'tipos_servicios' => $tiposServicios,
            'marcas' => $marcas,
            'modelos' => $modelos,
        ];

        return $response;
    }

    public function update_configs(Request $request)
    {
        try {
            Config::where('id', 1)->update([
                'correo_financiacion' => $request->correo_financiacion,
                'correo_contacto' => $request->correo_contacto,
                'telefono_contacto' => $request->telefono_contacto,
                'link_video' => $request->link_video,
                'tyc' => $request->tyc,
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

    public function permissions(Request $request)
    {
        $dataUser = $request->session()->get('key');
        $permissions = Permissions::select('permissions.id', 'permissions.permission', 'permissions.slug')
            ->join('permissions-users AS PU', 'PU.permission_id', 'permissions.id')
            ->where('PU.rol_id', $dataUser->rol_id)
            ->get();

        $response = [
            'permissions' => $permissions
        ];

        return $response;
    }

    public function get_by_permissions(Request $request)
    {
        $modules = Modules::all();
        $permissions = Permissions::all();
        $permissionsRol = Permissions::select('permissions.id', 'permissions.permission', 'permissions.slug')
            ->join('permissions-users AS PU', 'PU.permission_id', 'permissions.id')
            ->where('PU.rol_id', $request->id)
            ->get();
        $rol = Roles::where('id', $request->id)->first();

        $allPermissions = [];
        foreach ($permissions as $permission) {
            $encontrado = false;
            foreach ($permissionsRol as $permissionRol) {
                if ($permission->slug === $permissionRol->slug) {
                    $encontrado = true;
                    array_push($allPermissions, (object) array(
                        'id' => $permission->id, 'name' => $permission->permission, 'slug' => $permission->slug, 'module' => $permission->module_id, 'checked' => true
                    ));
                    break;
                }
            }
            if (!$encontrado) {
                array_push($allPermissions, (object) array(
                    'id' => $permission->id, 'name' => $permission->permission, 'slug' => $permission->slug, 'module' => $permission->module_id, 'checked' => false
                ));
            }
        }

        $response = [
            'permissions' => $allPermissions,
            'modules' => $modules,
            'rol' => $rol,
        ];

        return $response;
    }

    public function form_permissions(Request $request)
    {
        $modules = Modules::all();
        $permissions = Permissions::all();

        $allPermissions = [];
        foreach ($permissions as $permission) {
            array_push($allPermissions, (object) array(
                'id' => $permission->id, 'name' => $permission->permission, 'slug' => $permission->slug, 'module' => $permission->module_id, 'checked' => false
            ));
        }

        $response = [
            'permissions' => $allPermissions,
            'modules' => $modules,
        ];

        return $response;
    }

    public function update_permissions(Request $request)
    {
        try {
            $rol = Roles::where('nombre', $request->nombre)->where('id', '<>', $request->id)->get();
            if (COUNT($rol) === 0) {
                \DB::table('roles')->where('id', $request->id)->update(['nombre' => $request->nombre]);
                PermissionsUsers::where('rol_id', $request->id)->delete();

                foreach ($request->permissions as $permission) {
                    PermissionsUsers::insert([
                        'rol_id' => $request->id,
                        'permission_id' => $permission["id"]
                    ]);
                }

                $response = [
                    'status' => true,
                    'message' => 'Datos actualizados correctamente!'
                ];
            } else {
                $response = [
                    'status' => false,
                    'message' => 'Ya otro rol con este mismo nombre!'
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

    public function create_permissions(Request $request)
    {
        try {
            $rol = Roles::where('nombre', $request->nombre)->get();
            if (COUNT($rol) === 0) {
                $rol_id = Roles::insertGetId(['nombre' => $request->nombre]);

                foreach ($request->permissions as $permission) {
                    PermissionsUsers::insert([
                        'rol_id' => $rol_id,
                        'permission_id' => $permission["id"]
                    ]);
                }

                $response = [
                    'status' => true,
                    'message' => 'Datos actualizados correctamente!'
                ];
            } else {
                $response = [
                    'status' => false,
                    'message' => 'Ya otro rol con este mismo nombre!'
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

    public function get_by_marks(Request $request)
    {
        $marca = '';
        if ($request->id != 0) {
            $marca = Marcas::select('id', 'nombre', 'categoria_id')->where('id', $request->id)->first();
        }
        $categorias = TipoVehiculos::all();

        $response = [
            'marca' => $marca,
            'categorias' => $categorias
        ];

        return $response;
    }

    public function update_marks(Request $request)
    {
        try {
            \DB::table('marcas')->where('id', $request->id)->update([
                'nombre' => $request->nombre,
                'categoria_id' => $request->categoria_id,
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

    public function create_marks(Request $request)
    {
        try {
            Marcas::insert([
                'nombre' => $request->nombre,
                'categoria_id' => $request->categoria_id,
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

    public function delete_marks(Request $request)
    {
        \DB::table('modelos')->where('marca_id', $request->id)->delete();
        \DB::table('marcas')->where('id', $request->id)->delete();

        $marcas = Marcas::select('marcas.id', 'marcas.nombre', 'TV.nombre AS nombrePadre')
            ->join('tipo_vehiculos AS TV', 'TV.id', 'marcas.categoria_id')
            ->get();
        $modelos = Modelos::select('modelos.id', 'modelos.nombre', 'M.nombre AS nombrePadre')
            ->join('marcas AS M', 'M.id', 'modelos.marca_id')
            ->get();

        $response = [
            'status' => true,
            'marcas' => $marcas,
            'modelos' => $modelos,
        ];

        return $response;
    }

    public function get_by_models(Request $request)
    {
        $modelo = '';
        $marcas = [];
        if ($request->id != 0) {
            $modelo = Modelos::select('modelos.id', 'modelos.nombre', 'modelos.marca_id', 'M.categoria_id')
            ->join('marcas AS M', 'M.id', 'modelos.marca_id')
            ->where('modelos.id', $request->id)
            ->first();
            $marcas = Marcas::where('categoria_id', $modelo->categoria_id)->get();
        }
        
        $marcasAll = Marcas::all();
        $categorias = TipoVehiculos::all();

        $response = [
            'modelo' => $modelo,
            'marcas' => $marcas,
            'marcasAll' => $marcasAll,
            'categorias' => $categorias
        ];

        return $response;
    }

    public function create_models(Request $request)
    {
        try {
            Modelos::insert([
                'marca_id' => $request->marca_id,
                'nombre' => $request->nombre,
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

    public function update_models(Request $request)
    {
        try {
            \DB::table('modelos')->where('id', $request->id)->update([
                'marca_id' => $request->marca_id,
                'nombre' => $request->nombre,
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

    public function delete_models(Request $request)
    {
        \DB::table('modelos')->where('id', $request->id)->delete();

        $marcas = Marcas::select('marcas.id', 'marcas.nombre', 'TV.nombre AS nombrePadre')
            ->join('tipo_vehiculos AS TV', 'TV.id', 'marcas.categoria_id')
            ->get();
        $modelos = Modelos::select('modelos.id', 'modelos.nombre', 'M.nombre AS nombrePadre')
            ->join('marcas AS M', 'M.id', 'modelos.marca_id')
            ->get();

        $response = [
            'status' => true,
            'marcas' => $marcas,
            'modelos' => $modelos,
        ];

        return $response;
    }
}
