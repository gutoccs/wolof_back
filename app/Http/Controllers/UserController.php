<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $users = User::leftJoin('role_user', 'users.id', '=', 'role_user.user_id')
                ->leftJoin('roles', 'role_user.role_id', '=', 'roles.id');


        if($request->exists('id_user'))
            $users = $users->where('users.id', $request->id_user);

        if($request->exists('id_role'))
            $users = $users->where('roles.id', $request->id_role);

        if($request->exists('min_date'))
            $users = $users->where('users.created_at', '>=', $request->min_date);

        if($request->exists('max_date'))
            $users = $users->where('users.created_at', '<=', $request->max_date);


        if($request->exists('flag_login'))
        {
            if(in_array($request->flag_login, [0, 1]))
                $users = $users->where('users.flag_login', $request->flag_login);
        }


        if($request->exists('order_by'))
        {
            if(in_array($request->order_by, ['created_at_asc', 'created_at_desc']))
            {
                switch($request->order_by)
                {
                    case 'created_at_asc':      $users = $users->orderBy('users.created_at', 'asc');
                                                break;

                    case 'created_at_desc':     $users = $users->orderBy('users.created_at', 'desc');
                                                break;
                }
            }
        }


        $users = $users->select('users.id as id_user', 'users.email as email_user', 'role_user.role_id as id_role', 'roles.name as name_role', 'roles.slug as slug_role', 'users.flag_login as flag_login_user', 'users.observation_flag_login as observation_flag_login_user', 'users.created_at as created_at_user', 'users.updated_at as updated_at_user')
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
