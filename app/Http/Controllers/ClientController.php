<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Employee;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Http\Request;
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

        if($request->exists('id_role'))
            $clients = $clients->where('roles.id', $request->id_role);

        if($request->exists('min_date'))
            $clients = $clients->where('users.created_at', '>=', $request->min_date);

        if($request->exists('max_date'))
            $clients = $clients->where('users.created_at', '<=', $request->max_date);


        $clients = $clients->select('users.id as id_user', 'clients.id as id_client', 'users.email as email_user', 'users.username as username_user', 'clients.name as name_client', 'clients.surname as surname_client', 'users.cellphone_number as cellphone_number_user', 'clients.created_at as created_at_client', 'clients.updated_at as updated_at_client')
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
        // alpha_dash debe ser el username
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
                        ->where('clients.id_public', $idPublicClient)
                        ->select('users.id as id_user', 'clients.id as id_client', 'clients.id_public as id_client','users.email as email_user', 'users.username as username_user', 'clients.name as name_client', 'clients.surname as surname_client', 'users.cellphone_number as cellphone_number_user', 'clients.created_at as created_at_client', 'clients.updated_at as updated_at_client')
                        ->first();


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
        //
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
