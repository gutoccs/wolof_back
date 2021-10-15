<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use jeremykenedy\LaravelRoles\Models\Role;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'email'                 =>  'email|max:255|exists:users,email',
            'password'              =>  'string|between:8,255'
        ],
        [
            'email.email'           =>  'El Correo Electrónico tiene un formato inválido',
            'email.max'             =>  'El Correo Electrónico debe tener una longitud máxima de 255 caracteres',
            'email.exists'          =>  'El Correo Electrónico y/o la Contraseña no coinciden',
            'password.string'       =>  'La Contraseña contiene caracteres inválidos',
            'password.between'      =>  'La Contraseña debe contener entre 8 y 255 caracteres',
        ]);

        if($validator->fails())
            return response()->json(['errors'   =>  $validator->errors()], 422);

        if(User::where('email', $request->input('email'))->count() > 0) {

            $user = User::where('email', $request->input('email'))->first();

            if($user->flag_login == true) {
                $credentials = $request->only('email', 'password');
                if ($token = $this->guard()->attempt($credentials)) {
                    $userType;
                    $fullName;
                    if($user->hasRole(['ceo', 'cto', 'gabu.employee'])) {
                        $userType = 'employee';
                        $fullName = $user->employee->full_name;
                    }

                    if($user->hasRole(['commerce.owner', 'commerce.employee'])) {
                        $userType = 'merchant';
                        $fullName = $user->merchant->name . ' ' . $user->merchant->surname;
                    }

                    if($user->hasRole(['client'])) {
                        $userType = 'client';
                        $fullName = $user->client->name . ' ' . $user->client->surname;
                    }

                    return response()->json(
                        [
                            'status'            => 'success',
                            'token'             =>  $token,
                            'userRole'          =>  $user->getRoles()[0]->slug, // Posición 0 porque no se maneja Multi Roles
                            'userType'          =>  $userType,
                            'fullName'          =>  $fullName,
                            'avatarImage'       =>  ($user->avatar_profile_image) ? $user->avatar_profile_image : '',
                            'thumbnailImage'    =>  ($user->thumbnail_profile_image) ? $user->thumbnail_profile_image : '',
                        ], 200)->header('Authorization', $token);
                }
                return response()->json(['error' => 'El Correo Electrónico y/o la Contraseña no coinciden'], 422);
            }
            else {
                return response()->json(['error' => 'Usuario no tiene permiso de autenticarse'], 422);
            }
        }
        else return response()->json(['error' => 'Usuario no existe'], 422);
    }

    public function loginCellphoneNumber(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'cellphone_number'      =>  'string|between:4,32|exists:users,cellphone_number',
            'password'              =>  'string|between:8,255'
        ],
        [
            'cellphone_number.string'   =>  'El Teléfono Celular tiene caracteres inválidos',
            'cellphone_number.between'  =>  'El Teléfono Celular debe contener entre 4 y 32 caracteres',
            'cellphone_number.exists'   =>  'El Teléfono Celular y/o la Contraseña no coinciden',
            'password.string'           =>  'La Contraseña contiene caracteres inválidos',
            'password.between'          =>  'La Contraseña debe contener entre 8 y 255 caracteres',
        ]);

        if($validator->fails())
            return response()->json(['errors'   =>  $validator->errors()], 422);


        if(User::where('cellphone_number', $request->cellphone_number)->count() > 0) {

            $user = User::where('cellphone_number', $request->cellphone_number)->first();

            if($user->flag_login == true) {
                $credentials = ['email'   =>  $user->email, 'password' => $request->password];
                if ($token = $this->guard()->attempt($credentials)) {
                    $userType;
                    $fullName;
                    if($user->hasRole(['ceo', 'cto', 'gabu.employee'])) {
                        $userType = 'employee';
                        $fullName = $user->employee->full_name;
                    }

                    if($user->hasRole(['commerce.owner', 'commerce.employee'])) {
                        $userType = 'merchant';
                        $fullName = $user->merchant->name . ' ' . $user->merchant->surname;
                    }

                    if($user->hasRole(['client'])) {
                        $userType = 'client';
                        $fullName = $user->client->name . ' ' . $user->client->surname;
                    }

                    return response()->json(
                        [
                            'status'            => 'success',
                            'token'             =>  $token,
                            'userRole'          =>  $user->getRoles()[0]->slug, // Posición 0 porque no se maneja Multi Roles
                            'userType'          =>  $userType,
                            'fullName'          =>  $fullName,
                            'avatarImage'       =>  ($user->avatar_profile_image) ? $user->avatar_profile_image : '',
                            'thumbnailImage'    =>  ($user->thumbnail_profile_image) ? $user->thumbnail_profile_image : '',
                        ], 200)->header('Authorization', $token);
                }
                return response()->json(['error' => 'El Teléfono Celular y/o la Contraseña no coinciden'], 422);
            }
            else {
                return response()->json(['error' => 'Usuario no tiene permiso de autenticarse'], 422);
            }
        }
        else return response()->json(['error' => 'Usuario no existe'], 422);
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
