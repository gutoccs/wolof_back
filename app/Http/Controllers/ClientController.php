<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Employee;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $clients = Client::leftJoin('users', 'clients.user_id', '=', 'users.id')
                            ->leftJoin('role_user', 'role_user.user_id', '=', 'users.id')
                            ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
                            ->whereIn('roles.slug', ['client']);


        if($request->exists('id_user'))
            $clients = $clients->where('users.id', $request->id_user);

        if($request->exists('id_client'))
            $clients = $clients->where('clients.id', $request->id_client);

        if($request->exists('id_public'))
            $clients = $clients->where('clients.id_public', $request->id_public);

        if($request->exists('id_role'))
            $clients = $clients->where('roles.id', $request->id_role);

        if($request->exists('min_date'))
            $clients = $clients->where('users.created_at', '>=', $request->min_date);

        if($request->exists('max_date'))
            $clients = $clients->where('users.created_at', '<=', $request->max_date);


        if($request->exists('flag_login'))
        {
            if(in_array($request->flag_login, [0, 1]))
                $clients = $clients->where('users.flag_login', $request->flag_login);
        }

        if($request->exists('full_search'))
        {
            $fullSearch = $request->full_search;
            $clients = $clients->where(function($query) use ($fullSearch) {
                $query->orWhere('users.email', 'like', '%'.$fullSearch.'%')
                        ->orWhere('users.username', 'like', '%'.$fullSearch.'%')
                        ->orWhere('users.cellphone_number', 'like', '%'.$fullSearch.'%')
                        ->orWhere('clients.name', 'like', '%'.$fullSearch.'%')
                        ->orWhere('clients.surname', 'like', '%'.$fullSearch.'%');
            });
        }


        if($request->exists('order_by'))
        {
            if(in_array($request->order_by, ['created_at_asc', 'created_at_desc']))
            {
                switch($request->order_by)
                {
                    case 'created_at_asc':      $clients = $clients->orderBy('clients.created_at', 'asc');
                                                break;

                    case 'created_at_desc':     $clients = $clients->orderBy('clients.created_at', 'desc');
                                                break;
                }
            }
        }

        $clients = $clients->select('users.id as id_user', 'clients.id as id_client', 'clients.id_public as id_public_client', 'role_user.role_id as id_role', 'roles.name as name_role', 'roles.slug as slug_role', 'users.email as email_user', 'users.username as username_user', 'clients.name as name_client', 'clients.surname as surname_client', 'users.cellphone_number as cellphone_number_user', 'users.flag_login as flag_login_user', 'users.observation_flag_login as observation_flag_login_user', 'clients.created_at as created_at_client', 'clients.updated_at as updated_at_client')
                            ->get();

        return response()->json(
            [
                'status'    =>  'success',
                'clients'   =>  $clients
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
            'name'                  =>  'required|alpha|between:4,64',
            'surname'               =>  'required|alpha|between:4,64',
            'email'                 =>  'required|email|max:255|unique:users',
            'cellphone_number'      =>  'required|string|between:4,32|unique:users',
            'password'              =>  'required|string|max:255'
        ],
        [
            'name.required'             =>  'El Nombre es requerido',
            'name.alpha'                =>  'El Nombre debe contener solo Caracteres Alfabéticos',
            'name.between'              =>  'El Nombre debe debe contener entre 4 y 64 caracteres',
            'surname.required'          =>  'El Apellido es requerido',
            'surname.alpha'             =>  'El Apellido debe contener solo Caracteres Alfabéticos',
            'surname.between'           =>  'El Apellido debe contener entre 4 y 64 caracteres',
            'email.required'            =>  'El Correo Electrónico es requerido',
            'email.email'               =>  'El Correo Electrónico tiene un formato inválido',
            'email.max'                 =>  'El Correo Electrónico debe tener una longitud máxima de 255 caracteres',
            'email.unique'              =>  'El Correo Electrónico ya está siendo utilizado',
            'cellphone_number.required' =>  'El Teléfono Celular es requerido',
            'cellphone_number.string'   =>  'El Teléfono Celular tiene caracteres inválidos',
            'cellphone_number.between'  =>  'El Teléfono Celular debe contener entre 4 y 32 caracteres',
            'cellphone_number.unique'   =>  'El Teléfono Celular ya está siendo utilizado',
            'password.required'         =>  'La Contraseña es requerida',
            'password.string'           =>  'La Contraseña contiene caracteres inválidos',
            'password.max'              =>  'La Contraseña debe contener una longitud máxima de 255 caracteres',
        ]);

        if($validator->fails())
            return response()->json(['errors'   =>  $validator->errors()], 422);

        if(isset(Auth::user()->client->id) || isset(Auth::user()->merchant->id))
            return response()->json(['errors'   =>  'No tiene permiso para crear Clientes'], 422);


        $token = Str::random(24);

        $user = new User();
        $user->email = strtolower($request->email);
        $user->password = bcrypt($request->password);
        $user->cellphone_number = $request->cellphone_number;
        $user->flag_login = true;
        $user->observation_flag_login = 'Email sin verificar - ' . $token;

        if(!$user->save())
            return response()->json(['errors' => 'No se pudo crear el Usuario del Cliente'], 422);

        $client = new Client();

        $client->user_id = $user->id;
        $client->name = $request->name;
        $client->surname = $request->surname;

        $client->id_public = generateIdPublic();

        if(!$client->save())
        {
            $user->forceDelete();
                return response()->json(['errors'   =>  'No se pudo crear al Cliente'], 422);
        }

        $newRole = config('roles.models.role')::where('slug', '=', 'client')->first();
        $client->user->attachRole($newRole);

        return response()->json(['status' => 'success'], 200);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function show($idPublicClient)
    {
        if(Client::where('id_public', $idPublicClient)->count() == 0)
            return response()->json(['errors'   =>  'El Cliente no existe'], 422);

        $client = Client::leftJoin('users', 'clients.user_id', '=', 'users.id')
                        ->leftJoin('role_user', 'role_user.user_id', '=', 'users.id')
                        ->leftJoin('roles', 'roles.id', '=', 'role_user.role_id')
                        ->whereIn('roles.slug', ['client'])
                        ->where('clients.id_public', $idPublicClient);

        if(Auth::user()->hasRole(['ceo', 'cto', 'wolof.employee']))
            $client = $client->select('users.id as id_user', 'clients.id as id_client', 'clients.id_public as id_public_client', 'role_user.role_id as id_role', 'roles.name as name_role', 'roles.slug as slug_role', 'users.email as email_user', 'users.username as username_user', 'clients.name as name_client', 'clients.surname as surname_client', 'users.cellphone_number as cellphone_number_user', 'users.flag_login as flag_login_user', 'users.observation_flag_login as observation_flag_login_user', 'clients.created_at as created_at_client', 'clients.updated_at as updated_at_client');
        else
            $client = $client->select('users.id as id_user', 'clients.id as id_client', 'clients.id_public as id_public_client', 'roles.name as name_role', 'users.email as email_user', 'users.username as username_user', 'clients.name as name_client', 'clients.surname as surname_client', 'users.cellphone_number as cellphone_number_user', 'clients.created_at as created_at_client', 'clients.updated_at as updated_at_client');

        $client = $client->first();


        return response()->json(
            [
                'status'   =>  'success',
                'client'   =>  $client
            ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $idPublicClient)
    {

        $client = Client::where('id_public', $idPublicClient)->first();

        if(!$client)
            return response()->json(['errors'   =>  'El Cliente no existe'], 422);

        $validator = Validator::make($request->all(),
        [
            'name'                  =>  'alpha|between:4,64',
            'surname'               =>  'alpha|between:4,64',
            'email'                 =>  'email|max:255',
            'cellphone_number'      =>  'string|between:4,32',
            'password'              =>  'string|max:255'
        ],
        [
            'name.alpha'                =>  'El Nombre debe contener solo Caracteres Alfabéticos',
            'name.between'              =>  'El Nombre debe contener entre 4 y 64 caracteres',
            'surname.alpha'             =>  'El Apellido debe contener solo Caracteres Alfabéticos',
            'surname.between'           =>  'El Apellido debe contener entre 4 y 64 caracteres',
            'email.email'               =>  'El Correo Electrónico tiene un formato inválido',
            'email.max'                 =>  'El Correo Electrónico debe tener una longitud máxima de 255 caracteres',
            'cellphone_number.string'   =>  'El Teléfono Celular tiene caracteres inválidos',
            'cellphone_number.between'  =>  'El Teléfono Celular debe contener entre 4 y 32 caracteres',
            'password.string'           =>  'La Contraseña contiene caracteres inválidos',
            'password.max'              =>  'La Contraseña debe contener una longitud máxima de 255 caracteres',
        ]);

        if($validator->fails())
            return response()->json(['errors'   =>  $validator->errors()], 422);

        if(Auth::user()->hasRole(['client']))
        {
            if($client->id != Auth::user()->client->id)
                return response()->json(['errors'   =>  'Solo puede editar su perfil'], 422);
        }

        if($request->exists('name'))
            $client->name = $request->name;

        if($request->exists('surname'))
            $client->surname = $request->surname;

        if($request->exists('email'))
        {
            if(User::where('email', $request->email)->where('id', '!=', $client->user->id)->count() == 1)
                return response()->json(['errors'   =>  'El Correo Electrónico ya está siendo utilizado'], 422);

            if(User::where('email', $request->email)->count() == 0 && $client->user->email != $request->email)
                $client->user->email = $request->email;

            $token = Str::random(24);
            $client->user->observation_flag_login = 'Email sin verificar - ' . $token;
        }

        if($request->exists('cellphone_number'))
        {
            $request->cellphone_number = '+'. $request->cellphone_number;

            if(User::where('cellphone_number', $request->cellphone_number)->where('id', '!=', $client->user->id)->count() == 1)
                return response()->json(['errors'   =>  'El Teléfono Celular ya está siendo utilizado'], 422);

            if(User::where('cellphone_number', $request->cellphone_number)->count() == 0  && $client->user->cellphone_number != $request->cellphone_number)
                $client->user->cellphone_number = $request->cellphone_number;
        }

        if($request->exists('password'))
            $client->user->password = bcrypt($request->password);

        if($client->save() && $client->user->save())
            return response()->json(['status' => 'success'], 200);

        return response()->json(['errors'   =>  'No se pudo acualizar al Cliente'], 422);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function destroy($idPublicClient)
    {
        // TODO: Faltan realizar validaciones antes de eliminarlo

        Client::where('id_public', $idPublicClient)->first();

        if(!$client)
            return response()->json(['errors'   =>  'El Cliente no existe'], 422);

        if($client->user->delete())
            $client->delete();

        return response()->json(['status' => 'success'], 200);
    }
}
