<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


/*
* Paso 2 para recuperar la contraseña
* Validación de Token
*/

class CodeValidationController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'email'     =>  'required|email|exists:users',
            'token'     =>  'required|digits:6',
        ],
        [
            'email.required'        =>  'El Correo Electrónico es requerido',
            'email.email'           =>  'El Correo Electrónico tiene un formato incorrecto',
            'email.exists'          =>  'El Correo Electrónico no existe',
            'token.required'        =>  'El Código es requerido',
            'token.digits'          =>  'El Código debe se un número de 6 dígitos',
        ]);

        if($validator->fails())
            return response()->json(['errors'   =>  $validator->errors()], 422);

        $user = User::where('email', $request->email)->first();

        if(DB::table('password_resets')->where('email', $request->email)->exists())
        {
            $pR = DB::table('password_resets')->where('email', $request->email)->select('token')->first();

            if(Hash::check($request->token, $pR->token))
                return response()->json(['status'   =>  'success'], 200);
            else
                return response()->json(['error' => 'El Código es incorrecto'], 422);
        }
        else
            return response()->json(['error' => 'El usuario no ha iniciado el proceso para Recuperar su Contraseña'], 422);

    }
}
