<?php

namespace App\Http\Controllers;

use App\models\User;
use App\Mail\TokenResetPassword;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

/*
* Paso 1 para recuperar la contraseña
* Solicitud de Código de Validación
*/

class ForgotPasswordController extends Controller
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
            'email'     =>  'required|email|exists:users'
        ],
        [
            'email.required'    =>  'El Correo Electrónico es requerido',
            'email.email'       =>  'El Correo Electrónico tiene un formato incorrecto',
            'email.exists'      =>  'El Correo Electrónico no existe',
        ]);

        if($validator->fails())
            return response()->json(['errors'   =>  $validator->errors()], 422);

        $token = mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9);


        $user = User::where('email', $request->email)->first();

        $fullNameUser;

        if($user->hasRole(['ceo', 'cto', 'gabu.employee']))
            $fullNameUser = $user->employee->full_name;
        else if($user->hasRole(['commerce.owner', 'commerce.employee']))
            $fullNameUser = $user->merchant->name . ' ' . $user->merchant->surname;
        else
            $fullNameUser = $user->client->name . ' ' . $user->client->surname;


        if(DB::table('password_resets')->where('email', $user->email)->exists())
            DB::table('password_resets')->where('email', $user->email)->update(['token' => bcrypt($token), 'created_at' => Carbon::now()]);
        else
            DB::table('password_resets')->insert(['email' => $user->email, 'token' => bcrypt($token), 'created_at' => Carbon::now()]);

        try {
            Mail::to($user->email)->send(new TokenResetPassword($fullNameUser, $token));
        } catch(Exception $e) {
            Log::error("Error - Gabu App -> $e");
        }

        return response()->json(['status'   =>  'success'], 200);

    }
}
