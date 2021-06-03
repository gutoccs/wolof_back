<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::leftJoin('role_user', 'users.id', '=', 'role_user.user_id')
                ->leftJoin('roles', 'role_user.role_id', '=', 'roles.id')
                ->select('users.id as id_user', 'users.email as email_user', 'role_user.role_id as id_role', 'roles.name as name_role', 'roles.slug as slug_role')
                ->get();

        return response()->json(
            [
                'status' => 'success',
                'users' =>  $users
            ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($idUser)
    {
        $user = User::find($idUser);

        if($user) {
            return response()->json([
                'status'    =>  'success',
                'user'      =>  $user
            ], 200);
        }

        return response()->json(['error' => 'El Usuario no existe'], 422);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($idUser)
    {

        $user = User::find($idUser);

        if($user) {

            // TODO: Falta borrar su usuario cliente, empleado o comerciante


            if($user->delete()) {
                return response()->json([
                    'status'    =>  'success'
                ], 200);
            }

            return response()->json(['error' => 'No se pudo borrar al usuario'], 401);
        }

        return response()->json(['error' => 'El Usuario no existe'], 422);
    }
}
