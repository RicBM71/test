<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;

class UsersPermissionsController extends Controller
{

    public function show(User $user)
    {

        $heredados = $user->permisosHeredados();

        if (request()->wantsJson())
            return [
                'permissions' => $heredados,
                'user_permissions' => $user->getDirectPermissions()->pluck('name'),
                'permisos_heredados' => $user->getPermissionsViaRoles()->unique('name')->sortBy('nombre')->values(),
            ];

    }

	public function update(Request $request, User $user)
    {

        $k =  $request->input('permissions');

        $user->syncPermissions($request->input('permissions')); // esto es del paquete laravel permisions

        return response('Los permisos fueron actualizados',200);
    }
}
