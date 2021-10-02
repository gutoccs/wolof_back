<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DeveloperController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        return response()->json([
            'myName'    =>  'Gustavo Escobar Cobos',
            'role'      =>  'Mobile and Back End Developer',
            'myWeb'     =>  'https://gustavo-escobar.com',
            'myGitHub'  =>  'https://github.com/gutoccs',
        ], 200);
    }
}
