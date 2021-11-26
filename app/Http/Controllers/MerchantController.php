<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Employee;
use App\Models\Merchant;
use App\Models\Commerce;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
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
                            ->leftJoin('commerces', 'commerces.id', '=', 'merchants.commerce_id')
                            ->whereIn('roles.slug', ['commerce.owner', 'commerce.employee']);

        if(Auth::user()->hasRole(['commerce.employee', 'commerce.owner']))
            $merchants =  $merchants->where('merchants.commerce_id', Auth::user()->merchant->commerce_id);

        if($request->exists('id_user'))
             $merchants =  $merchants->where('users.id', $request->id_user);

        if($request->exists('id_merchant'))
             $merchants =  $merchants->where('merchants.id', $request->id_merchant);

        if($request->exists('id_public'))
            $merchants =  $merchants->where('merchants.id_public', $request->id_public);

        if($request->exists('id_role'))
             $merchants =  $merchants->where('roles.id', $request->id_role);

        if($request->exists('min_date'))
             $merchants =  $merchants->where('users.created_at', '>=', $request->min_date);

        if($request->exists('max_date'))
             $merchants =  $merchants->where('users.created_at', '<=', $request->max_date);

        if($request->exists('flag_login'))
        {
            if(in_array($request->flag_login, [0, 1]))
                $merchants = $merchants->where('users.flag_login', $request->flag_login);
        }

        if($request->exists('full_search'))
        {
            $fullSearch = $request->full_search;
            $merchants = $merchants->where(function($query) use ($fullSearch) {
                $query->orWhere('users.email', 'like', '%'.$fullSearch.'%')
                        ->orWhere('users.username', 'like', '%'.$fullSearch.'%')
                        ->orWhere('users.cellphone_number', 'like', '%'.$fullSearch.'%')
                        ->orWhere('merchants.name', 'like', '%'.$fullSearch.'%')
                        ->orWhere('merchants.surname', 'like', '%'.$fullSearch.'%');
            });
        }


        if($request->exists('order_by'))
        {
            if(in_array($request->order_by, ['created_at_asc', 'created_at_desc']))
            {
                switch($request->order_by)
                {
                    case 'created_at_asc':      $merchants = $merchants->orderBy('merchants.created_at', 'asc');
                                                break;

                    case 'created_at_desc':     $merchants = $merchants->orderBy('merchants.created_at', 'desc');
                                                break;
                }
            }
        }


        $merchants = $merchants->select('users.id as id_user', 'merchants.id as id_merchant', 'merchants.id_public as id_public_merchant', 'role_user.role_id as id_role', 'roles.name as name_role', 'roles.slug as slug_role', 'users.email as email_user', 'users.username as username_user', 'merchants.name as name_merchant', 'merchants.surname as surname_merchant', 'commerces.id as id_commerce', 'commerces.id_public as id_public_commerce', 'commerces.trade_name as trade_name_commerce', 'users.cellphone_number as cellphone_number_user', 'users.flag_login as flag_login_user', 'users.observation_flag_login as observation_flag_login_user', 'users.validated_email as validated_email_user', 'users.validated_mobile_number as validated_mobile_number_user', 'merchants.created_at as created_at_merchant', 'merchants.updated_at as updated_at_merchant')
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
            'name'                      =>  'required|string|between:2,64',
            'surname'                   =>  'required|string|between:2,64',
            'role_id'                   =>  'required|numeric|exists:roles,id|in:4,5',
            'commerce_id'               =>  'numeric|exists:commerces,id',
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
            'role_id.in'                        =>  'El Rol No es válido para un Comerciante',
            'commerce_id.numeric'               =>  'El ID del Comercio debe ser numérico',
            'commerce_id.exists'                =>  'El Comercio No Existe en la BD',
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
            return response()->json(['error' => 'No se pudo crear el Usuario del Empleado'], 422);

        $merchant = new Merchant();
        $merchant->user_id = $user->id;
        $merchant->name = $request->name;
        $merchant->surname = $request->surname;
        $merchant->id_public = generateIdPublic();

        if(Auth::user()->hasRole(['commerce.owner']))
        {
            $merchant->commerce_id = Auth::user()->merchant->commerce->id;
        }
        else
        {
            if(!$request->exists('commerce_id'))
                return response()->json(['error' => 'El ID del Comercio es requerido'], 422);

            $merchant->commerce_id = $request->commerce_id;
        }

        if(!$merchant->save())
        {
            $user->forceDelete();
            return response()->json(['error'   =>  'No se pudo crear al comerciante'], 422);
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
    public function show($idPublicMerchant)
    {
        if(Merchant::where('id_public', $idPublicMerchant)->count() == 0)
            return response()->json(['error'   =>  'El Comerciante no existe'], 422);

        $merchant = Merchant::leftJoin('users', 'merchants.user_id', '=', 'users.id')
                            ->leftJoin('role_user', 'role_user.user_id', '=', 'users.id')
                            ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
                            ->leftJoin('commerces', 'commerces.id', '=', 'merchants.commerce_id')
                            ->whereIn('roles.slug', ['commerce.owner', 'commerce.employee'])
                            ->where('merchants.id_public', $idPublicMerchant);

        if(Auth::user()->hasRole(['ceo', 'cto', 'gabu.employee']))
            $merchant = $merchant->select('users.id as id_user', 'merchants.id as id_merchant', 'merchants.id_public as id_public_merchant', 'role_user.role_id as id_role', 'roles.name as name_role', 'roles.slug as slug_role', 'users.email as email_user', 'users.username as username_user', 'merchants.name as name_merchant', 'merchants.surname as surname_merchant', 'commerces.id as id_commerce', 'commerces.id_public as id_public_commerce', 'commerces.trade_name as trade_name_commerce', 'users.cellphone_number as cellphone_number_user', 'users.flag_login as flag_login_user', 'users.observation_flag_login as observation_flag_login_user', 'users.validated_email as validated_email_user', 'users.validated_mobile_number as validated_mobile_number_user', 'merchants.created_at as created_at_merchant', 'merchants.updated_at as updated_at_merchant');
        else
            $merchant = $merchant->select('users.id as id_user', 'merchants.id as id_merchant', 'merchants.id_public as id_public_merchant', 'roles.name as name_role', 'users.email as email_user', 'users.username as username_user', 'merchants.name as name_merchant', 'merchants.surname as surname_merchant', 'commerces.id as id_commerce', 'commerces.id_public as id_public_commerce', 'commerces.trade_name as trade_name_commerce', 'users.cellphone_number as cellphone_number_user', 'users.validated_email as validated_email_user', 'users.validated_mobile_number as validated_mobile_number_user', 'merchants.created_at as created_at_merchant', 'merchants.updated_at as updated_at_merchant');


        if(Auth::user()->hasRole(['commerce.employee', 'commerce.owner']))
            $merchant =  $merchant->where('merchants.commerce_id', Auth::user()->merchant->commerce_id);


        $merchant = $merchant->first();

        return response()->json(
            [
                'status'        =>  'success',
                'merchant'      =>  $merchant
            ], 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Merchant  $merchant
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $idPublicMerchant)
    {
        $merchant = Merchant::where('id_public', $idPublicMerchant)->first();

        if(!$merchant)
            return response()->json(['error'   =>  'El Comerciante no existe'], 422);

        if(Auth::user()->hasRole('commerce.owner'))
        {
            if($merchant->commerce_id != Auth::user()->merchant->commerce_id)
                return response()->json(['error' => 'El Comerciante no pertenece a su Comercio'], 422);
        }

        $validator = Validator::make($request->all(),
        [
            'email'                     =>  'email',
            'password'                  =>  'min:8',
            'username'                  =>  'alpha_dash|between:4,64',
            'cellphone_number'          =>  'string|between:4,32',
            'name'                      =>  'string|between:4,64',
            'surname'                   =>  'string|between:4,64',
            'commerce_id'               =>  'numeric|exists:commerces,id',
        ],
        [
            'email.email'               =>  'Debe indicar un Correo Electrónico válido',
            'password.min'              =>  'La longitud mínima de la Contraseña es de 8 caracteres',
            'username.alpha_dash'       =>  'Solo se aceptan caracteres alfanuméricos, guiones y guiones bajos',
            'username.between'          =>  'La longitud del Nombre de Usuario debe ser entre 4 y 64 caracteres',
            'cellphone_number.string'   =>  'El Teléfono Celular tiene un formato inválido ',
            'cellphone_number.between'  =>  'La longitud del Teléfono Celular debe ser entre 4 y 32 caracteres',
            'name.string'               =>  'El Nombre es inválido',
            'name.between'              =>  'La longitud del Nombre es entre 4 y 64 caracteres',
            'surname.string'            =>  'El Apellido es inválido',
            'surname.between'           =>  'La longitud del Apellido es entre 4 y 64 caracteres',
            'commerce_id.numeric'       =>  'El ID del Comercio debe ser numérico',
            'commerce_id.exists'        =>  'El Comercio No Existe en la BD',
        ]);

        if($validator->fails())
            return response()->json(['errors'   =>  $validator->errors()], 422);

        if($request->exists('email'))
        {
            if(User::where('email', $request->email)->where('id', '!=', $merchant->user->id)->count() == 1)
                return response()->json(['error'   =>  'El Correo Electrónico ya está siendo utilizado'], 422);

            if(User::where('email', $request->email)->count() == 0 && $merchant->user->email != $request->email)
                $merchant->user->email = $request->email;
        }

        if($request->exists('password'))
            $merchant->user->password = bcrypt($request->password);

        if($request->exists('username'))
        {
            if(User::where('username', $request->username)->where('id', '!=', $merchant->user->id)->count() == 1)
                return response()->json(['error'   =>  'El Nombre de Usuario ya está siendo utilizado'], 422);

            if(User::where('username', $request->username)->count() == 0  && $merchant->user->username != $request->username)
                $merchant->user->username = strtolower($request->username);
        }

        if($request->exists('cellphone_number'))
        {
            if(User::where('cellphone_number', $request->cellphone_number)->where('id', '!=', $merchant->user->id)->count() == 1)
                return response()->json(['error'   =>  'El Teléfono Celular ya está siendo utilizado'], 422);

            if(User::where('cellphone_number', $request->cellphone_number)->count() == 0  && $merchant->user->cellphone_number != $request->cellphone_number)
                $merchant->user->cellphone_number = $request->cellphone_number;
        }

        if($request->exists('name'))
            $merchant->name = $request->name;

        if($request->exists('surname'))
            $merchant->surname = $request->surname;

        if(Auth::user()->hasRole(['ceo', 'cto', 'gabu.employee']))
        {
            if($request->exists('commerce_id'))
            {
                $merchant->commerce_id = $request->commerce_id;
            }
        }

        if($merchant->save() && $merchant->user->save())
            return response()->json(['status' => 'success'], 200);

        return response()->json(['error'   =>  'No se pudo acualizar al Comerciante'], 422);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Merchant  $merchant
     * @return \Illuminate\Http\Response
     */
    public function destroy($idPublicMerchant)
    {
        // TODO: Faltan realizar validaciones antes de eliminarlo

        $merchant = Merchant::where('id_public', $idPublicMerchant)->first();

        if(!$merchant)
            return response()->json(['error'   =>  'El Comerciante no existe'], 422);

        if(Auth::user()->hasRole('commerce.owner'))
        {
            if($merchant->commerce_id != Auth::user()->merchant->commerce_id)
                return response()->json(['error' => 'El Comerciante no pertenece a su Comercio'], 422);
        }

        if($merchant->user->delete())
            $merchant->delete();

        return response()->json(['status' => 'success'], 200);
    }

    public function changeRole(Request $request, $idPublicMerchant)
    {
        $merchant = Merchant::where('id_public', $idPublicMerchant)->first();

        if(!$merchant)
            return response()->json(['error'   =>  'El Comerciante no existe'], 422);

        if(Auth::user()->hasRole('commerce.owner'))
        {
            if($merchant->commerce_id != Auth::user()->merchant->commerce_id)
                return response()->json(['error' => 'El Comerciante no pertenece a su Comercio'], 422);
        }

        $validator = Validator::make($request->all(),
        [
            'role_id'               =>  'required|numeric|exists:roles,id|in:4,5',
        ],
        [
            'role_id.required'      =>  'El ID del Rol es requerido',
            'role_id.numeric'       =>  'El ID del Rol debe ser numérico',
            'role_id.exists'        =>  'El Rol No Existe en la BD',
            'role_id.in'            =>  'El Rol No es válido para un Comerciante'
        ]);

        if($validator->fails())
            return response()->json(['errors'   =>  $validator->errors()], 422);

        // Elimino todos los roles porque por ahora cada usuario solo tiene un rol
        $merchant->user->detachAllRoles();

        $newRole = config('roles.models.role')::find($request->role_id);
        $merchant->user->attachRole($newRole);

        return response()->json(['status' => 'success'], 200);

    }
}
