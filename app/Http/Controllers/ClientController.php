<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

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
    public function show(Client $client)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Client $client)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function destroy(Client $client)
    {
        //
    }
}
