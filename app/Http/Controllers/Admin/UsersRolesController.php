<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;

class UsersRolesController extends Controller
{

    public function show(User $user)
    {

        if (!esRoot())
            $roles = Role::with('permissions')->whereNotIn('name', ['Root'])->get();
        else
            $roles = Role::with('permissions')->get();


        if (request()->wantsJson())
            return [
                'roles' => $roles->pluck('name'),
                'user_role' => $user->getRoleNames(),
                'permisos_heredados' => $user->getPermissionsViaRoles()->unique('name')->sortBy('nombre')->values(),
                'user_permissions' => $user->permisosHeredados()
            ];

        return redirect()->route('home');
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        //return $request;
        //$role = Role::where('name', $request->input('roles'))->firstOrFail();

        //$role = [$role->name];
        $user->syncRoles($request->input('roles')); // esto es del paquete laravel permisions

        return [
            'permisos_heredados' => $user->getPermissionsViaRoles()->unique('name')->sortBy('nombre')->values(),
            'user_permissions' => $user->permisosHeredados()
        ];
    }

}
