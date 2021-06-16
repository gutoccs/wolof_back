<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use jeremykenedy\LaravelRoles\Models\Role;

class MerchantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $merchants = Merchant::leftJoin('users', 'merchants.user_id', '=', 'users.id')
                            ->leftJoin('role_user', 'role_user.user_id', '=', 'users.id')
                            ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
                            ->whereIn('roles.slug', ['commerce.owner', 'commerce.employee']);


        if($request->exists('id_user'))
             $merchants =  $merchants->where('users.id', $request->id_user);

        if($request->exists('id_merchant'))
             $merchants =  $merchants->where('merchants.id', $request->id_merchant);

        if($request->exists('id_role'))
             $merchants =  $merchants->where('roles.id', $request->id_role);

        if($request->exists('min_date'))
             $merchants =  $merchants->where('users.created_at', '>=', $request->min_date);

        if($request->exists('max_date'))
             $merchants =  $merchants->where('users.created_at', '<=', $request->max_date);


        $merchants = $merchants->select('users.id as id_user', 'merchants.id as id_merchant', 'users.email as email_user', 'users.username as username_user', 'merchants.name as name_merchant', 'merchants.surname as surname_merchant', 'users.cellphone_number as cellphone_number_user', 'merchants.created_at as created_at_merchant', 'merchants.updated_at as updated_at_merchant')
                                ->get();

        return response()->json(
            [
                'status'    =>  'success',
                'merchants'   =>  $merchants
            ], 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            'email'                     =>  'required|email|unique:users',
            'password'                  =>  'required|min:8|confirmed',
            'password_confirmation'     =>  'required|same:password',
            'cellphone_number'          =>  'required|string|between:4,32|unique:users',
            'name'                      =>  'required|string|between:4,64',
            'surname'                   =>  'required|string|between:4,64',
            'role_id'                   =>  'required|numeric|exists:roles,id|in:4,5',
        ],
        [
            'email.required'            =>  'El Correo Electrónico es requerido',
            'email.email'               =>  'Debe indicar un Correo Electrónico válido',
            'email.unique'              =>  'El Correo Electrónico ya está en uso',
            'password.required'         =>  'La contraseña es requerida',
            'password.min'              =>  'La longitud mínima de la Contraseña es de 8 caracteres',
            'password.confirmed'        =>  'Las Contraseñas no coinciden',
            'password_confirmation.required'    => 'La confirmación de la Contraseña es requerida',
            'password_confirmation.same'        => 'La confirmación de la Contraseña y la Contraseña no coinciden',
            'cellphone_number.required'         =>  'El Teléfono Celular es requerido',
            'cellphone_number.string'           =>  'El Teléfono Celular tiene un formato inválido ',
            'cellphone_number.between'          =>  'La longitud del Teléfono Celular debe ser entre 4 y 32 caracteres',
            'cellphone_number.unique'           =>  'El Teléfono Celular ya está en uso',
            'name.required'                     =>  'El Nombre es requerido',
            'name.string'                       =>  'El Nombre es inválido',
            'name.between'                      =>  'La longitud del Nombre es entre 4 y 64 caracteres',
            'surname.required'                  =>  'El Apellido es requerido',
            'surname.string'                    =>  'El Apellido es inválido',
            'surname.between'                   =>  'La longitud del Apellido es entre 4 y 64 caracteres',
            'role_id.required'                  =>  'El ID del Rol es requerido',
            'role_id.numeric'                   =>  'El ID del Rol debe ser numérico',
            'role_id.exists'                    =>  'El Rol No Existe en la BD',
            'role_id.in'                        =>  'El Rol No es válido para un Comerciante'
        ]);

        if($validator->fails())
            return response()->json(['errors'   =>  $validator->errors()], 422);

        $token = Str::random(24);

        $user = new User();
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->cellphone_number = $request->cellphone_number;
        $user->flag_login = true;
        $user->observation_flag_login = 'Email sin verificar - ' . $token;

        if(!$user->save())
            return response()->json(['errors' => 'No se pudo crear el Usuario del Empleado'], 422);

        $merchant = new Merchant();
        $merchant->user_id = $user->id;
        $merchant->name = $request->name;
        $merchant->surname = $request->surname;

        if(!$merchant->save())
        {
            $user->forceDelete();
            return response()->json(['errors'   =>  'No se pudo crear al comerciante'], 422);
        }

        $newRole = config('roles.models.role')::find($request->role_id);
        $merchant->user->attachRole($newRole);

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Merchant  $merchant
     * @return \Illuminate\Http\Response
     */
    public function show(Merchant $merchant)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Merchant  $merchant
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Merchant $merchant)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Merchant  $merchant
     * @return \Illuminate\Http\Response
     */
    public function destroy(Merchant $merchant)
    {
        //
    }
}
