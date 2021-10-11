<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        if(User::where('email', $request->input('email'))->count() > 0) {

            $user = User::where('email', $request->input('email'))->first();

            if($user->flag_login == true) {
                $credentials = $request->only('email', 'password');
                if ($token = $this->guard()->attempt($credentials)) {
                    return response()->json(['status' => 'success', 'token' => $token], 200)->header('Authorization', $token);
                }
                return response()->json(['error' => 'login_error'], 422);
            }
            else {
                return response()->json(['error' => 'login_error Usuario no tiene permiso de autenticarse'], 422);
            }
        }
        else return response()->json(['error' => 'login_error Usuario no existe'], 422);
    }

    public function logout()
    {
        $this->guard()->logout();
        return response()->json([
            'status' => 'success'
        ], 200);
    }

    public function user(Request $request)
    {
        $user = User::leftJoin('role_user', 'users.id', '=', 'role_user.user_id')
                    ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
                    ->select('users.id', 'users.email', 'users.username', 'role_user.role_id', 'roles.slug', 'roles.level')
                    ->where('users.id', Auth::user()->id)
                    ->first();
        /*
        if(Employee::where('user_id', Auth::user()->id)->first()){
            $user = $user->leftJoin('employees', 'users.id', '=', 'employees.user_id')
                        ->select('users.id as id', 'email', 'name', 'surname', 'role_user.role_id as role', 'employees.id as employee_id');
        }
        else {
            $user = $user->leftJoin('clients', 'users.id', '=', 'clients.user_id')
                        ->select('users.id as id', 'email', 'names as name', 'surnames as surname', 'role_user.role_id as role');
        }
        */


        return response()->json([
            'status'    => 'success',
            'data'      => $user
        ]);
    }

    public function refresh()
    {
        if ($token = $this->guard()->refresh()) {
            return response()
                ->json(['status' => 'successs'], 200)
                ->header('Authorization', $token);
        }
        return response()->json(['error' => 'refresh_token_error'], 422);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    public function payload()
    {
        return response()->json(auth()->payload());
    }

    private function guard()
    {
        return Auth::guard();
    }


}
