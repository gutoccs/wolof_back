<?php

namespace App\Http\Controllers;

use App\models\Client;
use App\Mail\TokenResetPassword;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

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

        $client = Client::leftJoin('users', 'users.id', '=', 'clients.user_id')
                            ->where('users.email', $request->email)
                            ->select('users.email as email', 'clients.name as name', 'clients.surname as surname')
                            ->first();

        $fullNameClient = $client->name . ' ' . $client->surname;

        if(DB::table('password_resets')->where('email', $client->email)->exists())
            DB::table('password_resets')->where('email', $client->email)->update(['token' => bcrypt($token), 'created_at' => Carbon::now()]);
        else
            DB::table('password_resets')->insert(['email' => $client->email, 'token' => bcrypt($token), 'created_at' => Carbon::now()]);

        try {
            Mail::to($client->email)->send(new TokenResetPassword($fullNameClient, $token));
        } catch(Exception $e) {
            Log::error("Error - Gabu App -> $e");
        }

        return response()->json(['status'   =>  'success'], 200);

    }
}
