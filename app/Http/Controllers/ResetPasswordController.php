<?php

namespace App\Http\Controllers;

use App\models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ResetPasswordController extends Controller
{

    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'email'     =>  'required|email|exists:users',
            'token'     =>  'required|digits:6',
            'password'  =>  'required|string|min:8|max:255'
        ],
        [
            'email.required'        =>  'El Correo Electrónico es requerido',
            'email.email'           =>  'El Correo Electrónico tiene un formato incorrecto',
            'email.exists'          =>  'El Correo Electrónico no existe',
            'token.required'        =>  'El Código es requerido',
            'token.digits'          =>  'El Código debe se un número de 6 dígitos',
            'password.required'     =>  'La Contraseña es requerida',
            'password.string'       =>  'La Contraseña contiene caracteres inválidos',
            'password.min'          =>  'La Contraseña debe contener una longitud mínima de 8 caracteres',
            'password.max'          =>  'La Contraseña debe contener una longitud máxima de 255 caracteres',
        ]);

        if($validator->fails())
            return response()->json(['errors'   =>  $validator->errors()], 422);

        $user = User::where('email', $request->email)->first();

        if(DB::table('password_resets')->where('email', $request->email)->exists())
        {
            $pR = DB::table('password_resets')->where('email', $request->email)->select('token')->first();

            if(Hash::check($request->token, $pR->token))
            {
                $this->resetPassword($user, $request->password);
                DB::table('password_resets')->where('email', $request->email)->delete();

                return response()->json(['status'   =>  'success'], 200);
            }
            else
                return response()->json(['error' => 'El Código es incorrecto'], 422);
        }
        else
            return response()->json(['error' => 'El usuario no ha iniciado el proceso para Recuperar su Contraseña'], 422);

    }

    protected function resetPassword($user, $password)
    {
        $user->forceFill([
            'password' => bcrypt($password),
            'remember_token' => str_random(60),
        ])->save();

        // Aquí se podría generar el token para autenticar de una vez al usuario
        // $this->guard()->login($user);
    }
}
