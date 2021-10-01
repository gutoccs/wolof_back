<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ValidateAccount;
use App\Mail\ValidateAccount as ValidateAccountEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ValidateAccountController extends Controller
{
    public function startValidation(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'type'     =>  'required|in:"email","mobile_number"'
        ],
        [
            'type.required' =>  'El Tipo es requerido',
            'type.in'       =>  'Los valores de Tipo debe ser email o mobile_number',
        ]);

        if($validator->fails())
            return response()->json(['errors'   =>  $validator->errors()], 422);

        if($request->type == 'email')
        {
            if(Auth::user()->validated_email)
                return response()->json(['errors'   =>  'Su Correo Electrónico ya está validado'], 422);
        }

        if($request->type == 'mobile_number')
        {
            if(Auth::user()->validated_mobile_number)
                return response()->json(['errors'   =>  'Su Teléfono Celular ya está validado'], 422);
        }


        $token = mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9) . mt_rand(0,9);

        $fullNameUser;

        if(Auth::user()->hasRole(['ceo', 'cto', 'wolof.employee']))
            $fullNameUser = Auth::user()->employee->full_name;
        else if(Auth::user()->hasRole(['commerce.owner', 'commerce.employee']))
            $fullNameUser = Auth::user()->merchant->name . ' ' . Auth::user()->merchant->surname;
        else
            $fullNameUser = Auth::user()->client->name . ' ' . Auth::user()->client->surname;

        $validateAccount = ValidateAccount::where('user_id', Auth::user()->id)
                                            ->where('type', $request->type)
                                            ->first();

        if($validateAccount)
        {
            $validateAccount->token = bcrypt($token);
            $validateAccount->save();
        }
        else
        {
            $validateAccount = new ValidateAccount();
            $validateAccount->user_id = Auth::user()->id;
            $validateAccount->token = bcrypt($token);
            $validateAccount->type = $request->type;
            $validateAccount->save();
        }

        try {
            Mail::to(Auth::user()->email)->send(new ValidateAccountEmail($fullNameUser, $token, $request->type));
        } catch(Exception $e) {
            Log::error("Error - Gabu App -> $e");
        }

        return response()->json(['status'   =>  'success'], 200);

    }

    public function checkValidation(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'type'      =>  'required|in:"email","mobile_number"',
            'token'     =>  'required|digits:6',
        ],
        [
            'type.required'     =>  'El Tipo es requerido',
            'type.in'           =>  'Los valores de Tipo debe ser email o mobile_number',
            'token.required'    =>  'El Código es requerido',
            'token.digits'      =>  'El Código debe se un número de 6 dígitos',
        ]);

        if($validator->fails())
            return response()->json(['errors'   =>  $validator->errors()], 422);


        $validateAccount = ValidateAccount::where('user_id', Auth::user()->id)
                                            ->where('type', $request->type)
                                            ->first();

        if($validateAccount)
        {
            if(Hash::check($request->token, $validateAccount->token))
            {

                if($request->type == 'email')
                    Auth::user()->validated_email = true;

                if($request->type == 'mobile_number')
                    Auth::user()->validated_mobile_number = true;

                Auth::user()->save();

                $validateAccount->delete();

                return response()->json(['status'   =>  'success'], 200);
            }
            else
                return response()->json(['error' => 'El Código es incorrecto'], 422);
        }
        else
            return response()->json(['error' => 'El usuario no ha iniciado el proceso de Validación'], 422);

    }
}
