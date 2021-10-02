<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use \Gumlet\ImageResize;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $users = User::leftJoin('role_user', 'users.id', '=', 'role_user.user_id')
                ->leftJoin('roles', 'role_user.role_id', '=', 'roles.id');


        if($request->exists('id_user'))
            $users = $users->where('users.id', $request->id_user);

        if($request->exists('id_role'))
            $users = $users->where('roles.id', $request->id_role);

        if($request->exists('min_date'))
            $users = $users->where('users.created_at', '>=', $request->min_date);

        if($request->exists('max_date'))
            $users = $users->where('users.created_at', '<=', $request->max_date);


        if($request->exists('flag_login'))
        {
            if(in_array($request->flag_login, [0, 1]))
                $users = $users->where('users.flag_login', $request->flag_login);
        }

        if($request->exists('full_search'))
        {
            $fullSearch = $request->full_search;
            $users = $users->where(function($query) use ($fullSearch) {
                $query->orWhere('users.email', 'like', '%'.$fullSearch.'%')
                        ->orWhere('users.username', 'like', '%'.$fullSearch.'%')
                        ->orWhere('users.cellphone_number', 'like', '%'.$fullSearch.'%');
            });
        }


        if($request->exists('order_by'))
        {
            if(in_array($request->order_by, ['created_at_asc', 'created_at_desc']))
            {
                switch($request->order_by)
                {
                    case 'created_at_asc':      $users = $users->orderBy('users.created_at', 'asc');
                                                break;

                    case 'created_at_desc':     $users = $users->orderBy('users.created_at', 'desc');
                                                break;
                }
            }
        }


        $users = $users->select('users.id as id', 'users.email as email', 'users.username as username', 'users.cellphone_number as cellphone_number',  'role_user.role_id as id_role', 'roles.name as name_role', 'roles.slug as slug_role', 'users.flag_login as flag_login', 'users.observation_flag_login as observation_flag_login', 'users.validated_email as validated_email', 'users.validated_mobile_number as validated_mobile_number','users.created_at as created_at', 'users.updated_at as updated_at')
                        ->get();

        return response()->json(
            [
                'status' => 'success',
                'users' =>  $users
            ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($idUser)
    {

        if(User::where('id', $idUser)->count() == 0)
            return response()->json(['error' => 'El Usuario no existe'], 422);


        $user = User::leftJoin('role_user', 'users.id', '=', 'role_user.user_id')
                        ->leftJoin('roles', 'role_user.role_id', '=', 'roles.id')
                        ->where('users.id', $idUser)
                        ->select('users.id as id', 'users.email as email',  'users.username as username', 'users.cellphone_number as cellphone_number',  'role_user.role_id as id_role', 'roles.name as name_role', 'roles.slug as slug_role', 'users.flag_login as flag_login', 'users.observation_flag_login as observation_flag_login', 'users.validated_email as validated_email', 'users.validated_mobile_number as validated_mobile_number','users.created_at as created_at', 'users.updated_at as updated_at')
                        ->first();

        return response()->json([
            'status'    =>  'success',
            'user'      =>  $user
        ], 200);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($idUser)
    {

        $user = User::find($idUser);

        if($user) {

            // TODO: Falta borrar su usuario cliente, empleado o comerciante


            if($user->delete()) {
                return response()->json([
                    'status'    =>  'success'
                ], 200);
            }

            return response()->json(['error' => 'No se pudo borrar al usuario'], 401);
        }

        return response()->json(['error' => 'El Usuario no existe'], 422);
    }

    public function flagLogin(Request $request, $idUser)
    {
        $user = User::find($idUser);

        if(!$user)
            return response()->json(['error' => 'El Usuario no existe'], 422);

        $validator = Validator::make($request->all(),
        [
            'flag_login'                =>  'required|in:0,1'
        ],
        [
            'flag_login.required'       =>  'El campo flag_login es Requerido',
            'flag_login.in'             =>  'El valor de flag_login debe ser 0 o 1'
        ]);

        if($validator->fails())
            return response()->json(['errors'   =>  $validator->errors()], 422);

        if($request->flag_login == 0)
        {
            $validator = Validator::make($request->all(),
            [
                'observation_flag_login'            =>  'required|string|max:255'
            ],
            [
                'observation_flag_login.required'       =>  'El campo observation_flag_login es Requerido',
                'observation_flag_login.string'         =>  'El observation_flag_login debe ser un String',
                'observation_flag_login.max'            =>  'observation_flag_login debe ser máximo 255 caracteres'
            ]);

            if($validator->fails())
                return response()->json(['errors'   =>  $validator->errors()], 422);

            $user->flag_login = false;
            $user->observation_flag_login = $request->observation_flag_login;
        }
        else {
            $user->flag_login = true;
            $user->observation_flag_login = null;
        }

        if($user->hasRole(['ceo', 'cto', 'gabu.employee']))
        {
            if(Auth::user()->hasRole(['ceo', 'cto']))
            {
                if($user->save())
                    return response()->json(['status'    =>  'success'], 200);
            }
            else{
                return response()->json(['error' => 'Solo CEO y CTO puede realizar esta solicitud'], 422);
            }
        }
        else {
            if($user->save())
                return response()->json(['status'    =>  'success'], 200);
        }

        return response()->json(['error' => 'No se pudo actualizar flag_login del usuario'], 422);
    }

    public function updateProfileImage(Request $request)
    {
        // TODO: ratio:1/1 en dimensions, la imagen debe venir cuadrada del mobile

        $validator = Validator::make($request->all(),
        [
            'image' =>  'required|file|max:3072|dimensions:min_width=300,max_width=3200,min_height=300,max_height=3200|mimes:jpeg,bmp,png'
        ],
        [
            'image.required'        =>  'La imagen es requerida',
            'image.file'            =>  'La imagen debe ser un tipo de archivo',
            'image.max'             =>  'La imagen debe tener un peso máximo de 3MB',
            'image.dimensions'      =>  'El tamaño de la imagen debe estar entre 300px y 3200px',
            'image.mimes'           =>  'La imagen debe ser jpg, bmp o png'
        ]);

        if($validator->fails())
            return response()->json(['errors' => $validator->errors()], 422);

        $userId;

        if(Auth::user()->hasRole(['ceo', 'cto', 'gabu.employee']))
        {
            $validator = Validator::make($request->all(),
            [
                'user_id'           =>  'exists:users,id'
            ],
            [
                'user_id.exists'    =>  'El usuario no existe',
            ]);

            if($validator->fails())
                return response()->json(['errors' => $validator->errors()], 422);

            if($request->exists('user_id'))
                $userId = $request->user_id;
            else
                $userId = Auth::user()->id;

        }
        else
        {
            $userId = Auth::user()->id;
        }

        $user = User::find($userId);

        $auxPath = "files/users/profile-image/" . $user->id;
        $path = public_path($auxPath);
        File::deleteDirectory($path); //Garantiza de borrar las imágenes de perfil anterior
        Storage::makeDirectory($path);

        //original_profile_image
        $auxIMG = $request->image;
        $extension = $auxIMG->extension();
        $originalName = str_replace(' ','', $auxIMG->getClientOriginalName());
        $auxIMG->move($path, $originalName);

        $fullPathOriginalImage = $auxPath . '/' . $originalName;
        $user->original_profile_image = $auxPath . '/' . $originalName;

        $fullPathNewImage = $auxPath . '/' . Str::random(12) . '.' . $extension;
        $image = new ImageResize($fullPathOriginalImage);
        $image->resize(180, 180);
        $image->save($fullPathNewImage);
        $user->thumbnail_profile_image = $fullPathNewImage;

        $fullPathNewImage = $auxPath . '/' . Str::random(12) . '.' . $extension;
        $image = new ImageResize($fullPathOriginalImage);
        $image->resize(60, 60);
        $image->save($fullPathNewImage);
        $user->avatar_profile_image = $fullPathNewImage;

        $user->save();

        return response()->json(['status' => 'success'], 200);

    }

    public function removeProfileImage(Request $request)
    {
        $userId;

        if(Auth::user()->hasRole(['ceo', 'cto', 'gabu.employee']))
        {
            $validator = Validator::make($request->all(),
            [
                'user_id'           =>  'exists:users,id'
            ],
            [
                'user_id.exists'    =>  'El usuario no existe',
            ]);

            if($validator->fails())
                return response()->json(['errors' => $validator->errors()], 422);

            if($request->exists('user_id'))
                $userId = $request->user_id;
            else
                $userId = Auth::user()->id;

        }
        else
        {
            $userId = Auth::user()->id;
        }

        $user = User::find($userId);

        $auxPath = "files/users/profile-image/" . $user->id;
        $path = public_path($auxPath);
        File::deleteDirectory($path); //Garantiza de borrar las imágenes de perfil anterior

        $user->original_profile_image = null;
        $user->thumbnail_profile_image = null;
        $user->avatar_profile_image = null;

        $user->save();

        return response()->json(['status' => 'success'], 200);
    }

}
