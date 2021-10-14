<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Commerce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use jeremykenedy\LaravelRoles\Models\Role;
use Illuminate\Support\Str;
use \Gumlet\ImageResize;

class CommerceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $commerces;

        if(Auth::user()->hasRole(['ceo', 'cto', 'gabu.employee']))
            $commerces =  Commerce::select('commerces.id as id_commerce', 'commerces.id_public as id_public_commerce', 'commerces.trade_name as trade_name_commerce', 'commerces.legal_name as legal_name_commerce', 'commerces.tax_identification_number as tax_identification_number_commerce', 'commerces.short_description as short_description_commerce', 'commerces.slogan as slogan_commerce', 'commerces.original_profile_image as original_profile_image_commerce', 'commerces.thumbnail_profile_image as thumbnail_profile_image_commerce', 'commerces.avatar_profile_image as avatar_profile_image_commerce', 'commerces.flag_active as flag_active_commerce', 'commerces.observation_flag_active as observation_flag_active_commerce', 'commerces.created_at as created_at_commerce', 'commerces.address as address_commerce');


        if(Auth::user()->hasRole(['commerce.owner', 'commerce.employee']))
            $commerces =  Commerce::select('commerces.id_public as id_public_commerce', 'commerces.trade_name as trade_name_commerce', 'commerces.legal_name as legal_name_commerce', 'commerces.tax_identification_number as tax_identification_number_commerce', 'commerces.short_description as short_description_commerce', 'commerces.slogan as slogan_commerce', 'commerces.original_profile_image as original_profile_image_commerce', 'commerces.thumbnail_profile_image as thumbnail_profile_image_commerce', 'commerces.avatar_profile_image as avatar_profile_image_commerce', 'commerces.created_at as created_at_commerce', 'commerces.address as address_commerce',);

        if(Auth::user()->hasRole('client'))
            $commerces =  Commerce::select('commerces.id_public as id_public_commerce', 'commerces.trade_name as trade_name_commerce', 'commerces.short_description as short_description_commerce', 'commerces.slogan as slogan_commerce', 'commerces.original_profile_image as original_profile_image_commerce', 'commerces.thumbnail_profile_image as thumbnail_profile_image_commerce', 'commerces.avatar_profile_image as avatar_profile_image_commerce', 'commerces.address as address_commerce',);


        if($request->exists('id_commerce'))
            $commerces =  $commerces->where('commerces.id', $request->id_commerce);

         if($request->exists('id_public'))
            $commerces =  $commerces->where('commerces.id_public', $request->id_public);

        if($request->exists('min_date'))
            $commerces =  $commerces->where('commerces.created_at', '>=', $request->min_date);

        if($request->exists('max_date'))
            $commerces =  $commerces->where('commerces.created_at', '<=', $request->max_date);

        if($request->exists('flag_active'))
        {
            if(in_array($request->flag_active, [0, 1]))
                $commerces = $commerces->where('commerces.flag_active', $request->flag_active);
        }

        if($request->exists('full_search'))
        {
            $fullSearch = $request->full_search;
            $commerces = $commerces->where(function($query) use ($fullSearch) {
                $query->orWhere('commerces.trade_name', 'like', '%'.$fullSearch.'%')
                        ->orWhere('commerces.legal_name', 'like', '%'.$fullSearch.'%')
                        ->orWhere('commerces.tax_identification_number', 'like', '%'.$fullSearch.'%');
            });
        }

        if($request->exists('order_by'))
        {
            if(in_array($request->order_by, ['created_at_asc', 'created_at_desc']))
            {
                switch($request->order_by)
                {
                    case 'created_at_asc':      $commerces = $commerces->orderBy('commerces.created_at', 'asc');
                                                break;

                    case 'created_at_desc':     $commerces = $commerces->orderBy('commerces.created_at', 'desc');
                                                break;
                }
            }
        }


        $commerces = $commerces->get();

        return response()->json(
            [
                'status'        =>  'success',
                'commerces'     =>  $commerces
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
            'trade_name'                    =>  'required|max:128|unique:commerces',
            'legal_name'                    =>  'max:128|unique:commerces',
            'tax_identification_number'     =>  'max:64|unique:commerces',
            'short_description '            =>  'max:255',
            'slogan'                        =>  'max:255',
            'address'                       =>  'max:255'
        ],
        [
            'trade_name.required'               =>  'El Nombre Comercial es requerido',
            'trade_name.max'                    =>  'El Nombre Comercial no puede exceder los 128 caracteres',
            'trade_name.unique'                 =>  'El Nombre Comercial ya está siendo usado',
            'legal_name.max'                    =>  'El Nombre Legal no puede exceder los 128 caracteres',
            'legal_name.unique'                 =>  'El Nombre Legal ya está siendo usado',
            'tax_identification_number.max'     =>  'El NIT no puede exceder los 64 caracteres',
            'tax_identification_number.unique'  =>  'El NIT ya está siendo usado',
            'short_description.max'             =>  'La Descripción Corta del Comercio no puede exceder los 255 caracteres',
            'slogan.max'                        =>  'El Eslogan no puede exceder los 255 caracteres',
            'address.max'                       =>  'La Dirección no puede exceder los 255 caracteres'
        ]);

        if($validator->fails())
            return response()->json(['errors'   =>  $validator->errors()], 422);

        $commerce = new Commerce();

        $commerce->id_public = generateIdPublic();

        $commerce->trade_name = $request->trade_name;

        if($request->exists('legal_name'))
            $commerce->legal_name = $request->legal_name;

        if($request->exists('tax_identification_number'))
            $commerce->tax_identification_number = $request->tax_identification_number;

        if($request->exists('short_description'))
            $commerce->short_description = $request->short_description;

        if($request->exists('slogan'))
            $commerce->slogan = $request->slogan;

        if($request->exists('address'))
            $commerce->address = $request->address;

        if(!$commerce->save())
            return response()->json(['error'   =>  'No se pudo guardar el Comercio'], 422);

        $contact = new Contact();

        $contact->commerce_id = $commerce->id;

        if($contact->save())
            return response()->json(['status'   =>  'success'], 200);

        $commerce->forceDelete();

        return response()->json(['error'   =>  'No se pudo guardar el Contacto ni el Comercio'], 422);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Commerce  $commerce
     * @return \Illuminate\Http\Response
     */
    public function show($idPublicCommerce)
    {
        if(Commerce::where('id_public', $idPublicCommerce)->count() == 0)
            return response()->json(['error'   =>  'El Comercio no existe'], 422);


        if(Auth::user()->hasRole(['ceo', 'cto', 'gabu.employee']))
            $commerce =  Commerce::select('commerces.id as id_commerce', 'commerces.id_public as id_public_commerce', 'commerces.trade_name as trade_name_commerce', 'commerces.legal_name as legal_name_commerce', 'commerces.tax_identification_number as tax_identification_number_commerce', 'commerces.short_description as short_description_commerce', 'commerces.slogan as slogan_commerce', 'commerces.original_profile_image as original_profile_image_commerce', 'commerces.thumbnail_profile_image as thumbnail_profile_image_commerce', 'commerces.avatar_profile_image as avatar_profile_image_commerce', 'commerces.flag_active as flag_active_commerce', 'commerces.observation_flag_active as observation_flag_active_commerce', 'commerces.created_at as created_at_commerce', 'commerces.address as address_commerce');


        if(Auth::user()->hasRole(['commerce.owner', 'commerce.employee']))
            $commerce =  Commerce::select('commerces.id_public as id_public_commerce', 'commerces.trade_name as trade_name_commerce', 'commerces.legal_name as legal_name_commerce', 'commerces.tax_identification_number as tax_identification_number_commerce', 'commerces.short_description as short_description_commerce', 'commerces.slogan as slogan_commerce', 'commerces.original_profile_image as original_profile_image_commerce', 'commerces.thumbnail_profile_image as thumbnail_profile_image_commerce', 'commerces.avatar_profile_image as avatar_profile_image_commerce', 'commerces.created_at as created_at_commerce', 'commerces.address as address_commerce',);

        if(Auth::user()->hasRole('client'))
            $commerce =  Commerce::select('commerces.id_public as id_public_commerce', 'commerces.trade_name as trade_name_commerce', 'commerces.short_description as short_description_commerce', 'commerces.slogan as slogan_commerce', 'commerces.original_profile_image as original_profile_image_commerce', 'commerces.thumbnail_profile_image as thumbnail_profile_image_commerce', 'commerces.avatar_profile_image as avatar_profile_image_commerce', 'commerces.address as address_commerce',);



        $commerce = $commerce->where('commerces.id_public', $idPublicCommerce)
                                ->first();

        return response()->json(
            [
                'status'        =>  'success',
                'commerce'      =>  $commerce
            ], 200);


    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Commerce  $commerce
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $idPublicCommerce)
    {
        $commerce = Commerce::where('id_public', $idPublicCommerce)->first();

        if(!$commerce)
            return response()->json(['error'   =>  'El Comercio no existe'], 422);

        if(Auth::user()->hasRole('commerce.owner'))
        {
            if(Auth::user()->merchant->commerce_id != $commerce->id)
                return response()->json(['error'   =>  'El Comercio no le pertenece'], 422);
        }

        $validator = Validator::make($request->all(),
        [
            'trade_name'                    =>  'max:128',
            'legal_name'                    =>  'max:128',
            'tax_identification_number'     =>  'max:64',
            'short_description '            =>  'max:255',
            'slogan'                        =>  'max:255',
            'address'                       =>  'max:255'
        ],
        [
            'trade_name.max'                    =>  'El Nombre Comercial no puede exceder los 128 caracteres',
            'legal_name.max'                    =>  'El Nombre Legal no puede exceder los 128 caracteres',
            'tax_identification_number.max'     =>  'El NIT no puede exceder los 64 caracteres',
            'short_description.max'             =>  'La Descripción Corta del Comercio no puede exceder los 255 caracteres',
            'slogan.max'                        =>  'El Eslogan no puede exceder los 255 caracteres',
            'address.max'                       =>  'La Dirección no puede exceder los 255 caracteres'
        ]);

        if($validator->fails())
            return response()->json(['errors'   =>  $validator->errors()], 422);

        if($request->exists('trade_name'))
        {
            if(Commerce::where('trade_name', $request->trade_name)->where('id', '!=', $commerce->id)->count() == 1)
                return response()->json(['error'   =>  'El Nombre Comercial ya está siendo utilizado'], 422);

            if(Commerce::where('trade_name', $request->trade_name)->count() == 0  && $commerce->trade_name != $request->trade_name)
                $commerce->trade_name = $request->trade_name;
        }


        if($request->exists('legal_name'))
        {
            if(Commerce::where('legal_name', $request->legal_name)->where('id', '!=', $commerce->id)->count() == 1)
                return response()->json(['error'   =>  'El Nombre Legal ya está siendo utilizado'], 422);

            if(Commerce::where('legal_name', $request->legal_name)->count() == 0  && $commerce->legal_name != $request->legal_name)
                $commerce->legal_name = $request->legal_name;
        }


        if($request->exists('tax_identification_number'))
        {
            if(Commerce::where('tax_identification_number', $request->tax_identification_number)->where('id', '!=', $commerce->id)->count() == 1)
                return response()->json(['error'   =>  'El NIT ya está siendo utilizado'], 422);

            if(Commerce::where('tax_identification_number', $request->tax_identification_number)->count() == 0  && $commerce->tax_identification_number != $request->tax_identification_number)
                $commerce->tax_identification_number = $request->tax_identification_number;
        }


        if($request->exists('short_description'))
            $commerce->short_description = $request->short_description;

        if($request->exists('slogan'))
            $commerce->slogan = $request->slogan;

        if($request->exists('address'))
            $commerce->address = $request->address;

        if($commerce->save())
            return response()->json(['status'   =>  'success'], 200);

        return response()->json(['error'   =>  'No se pudo actualizar el Comercio'], 422);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Commerce  $commerce
     * @return \Illuminate\Http\Response
     */
    public function destroy($idPublicCommerce)
    {
        // No aplica, para ello existe la variable flag_active
    }

    public function flagActive(Request $request, $idPublicCommerce)
    {
        $commerce = Commerce::where('id_public', $idPublicCommerce)->first();

        if(!$commerce)
            return response()->json(['error'   =>  'El Comercio no existe'], 422);

        $validator = Validator::make($request->all(),
        [
            'flag_active'           =>  'required|boolean'
        ],
        [
            'flag_active.required'   =>  'flag_active es requerido',
            'flag_active.boolean'    =>  'El valor de flag_active debe ser 0 o 1',
        ]);

        if($validator->fails())
            return response()->json(['errors'   =>  $validator->errors()], 422);

        if($request->flag_active == 0)
        {

            $validator = Validator::make($request->all(),
            [
                'observation_flag_active'           =>  'required|max:255'
            ],
            [
                'observation_flag_active.required'      =>  'observation_flag_active es requerido',
                'observation_flag_active.max'           =>  'observation_flag_active debe ser máximo de 255 caracteres',
            ]);

            if($validator->fails())
                return response()->json(['errors'   =>  $validator->errors()], 422);

            $commerce->flag_active = 0;
            $commerce->observation_flag_active = $request->observation_flag_active;
        }
        else
        {
            $commerce->flag_active = 1;
            $commerce->observation_flag_active = null;
        }

        if($commerce->save())
            return response()->json(['status'   =>  'success'], 200);

        return response()->json(['error'   =>  'No se pudo actualizar el Comercio'], 422);
    }

    public function updateProfileImage(Request $request)
    {
        // TODO: ratio:1/1 en dimensions, la imagen debe venir cuadrada del mobile

        $validator = Validator::make($request->all(),
        [
            'image' =>  'required|file|max:3072|dimensions:min_width=300,max_width=3200,min_height=300,max_height=3200|mimes:jpeg,bmp,png'
        ],
        [
            'image.required'        =>  'La Imagen es requerida',
            'image.file'            =>  'La Imagen debe ser un tipo de archivo',
            'image.max'             =>  'La Imagen debe tener un peso máximo de 3MB',
            'image.dimensions'      =>  'El tamaño de la Imagen debe estar entre 300px y 3200px',
            'image.mimes'           =>  'La Imagen debe ser jpg, bmp o png'
        ]);

        if($validator->fails())
            return response()->json(['errors' => $validator->errors()], 422);

        $commerceId;

        if(Auth::user()->hasRole(['ceo', 'cto', 'gabu.employee']))
        {

            $validator = Validator::make($request->all(),
            [
                'commerce_id'           =>  'required|exists:commerces,id'
            ],
            [
                'commerce_id.required'  =>  'El ID del Comercio es Requerido',
                'commerce_id.exists'    =>  'El Comercio no existe',
            ]);

            if($validator->fails())
                return response()->json(['errors' => $validator->errors()], 422);

            $commerceId = $request->commerce_id;
        }
        else
        {
            $commerceId = Auth::user()->merchant->commerce->id;
        }

        $commerce = Commerce::find($commerceId);

        $auxPath = "files/commerces/profile-image/" . $commerce->id;
        $path = public_path($auxPath);
        File::deleteDirectory($path); //Garantiza de borrar las imágenes de perfil anterior
        Storage::makeDirectory($path);

        //original_profile_image
        $auxIMG = $request->image;
        $extension = $auxIMG->extension();
        $originalName = str_replace(' ','', $auxIMG->getClientOriginalName());
        $auxIMG->move($path, $originalName);

        $fullPathOriginalImage = $auxPath . '/' . $originalName;
        $commerce->original_profile_image = $auxPath . '/' . $originalName;

        $fullPathNewImage = $auxPath . '/' . Str::random(12) . '.' . $extension;
        $image = new ImageResize($fullPathOriginalImage);
        $image->resize(180, 180);
        $image->save($fullPathNewImage);
        $commerce->thumbnail_profile_image = $fullPathNewImage;

        $fullPathNewImage = $auxPath . '/' . Str::random(12) . '.' . $extension;
        $image = new ImageResize($fullPathOriginalImage);
        $image->resize(60, 60);
        $image->save($fullPathNewImage);
        $commerce->avatar_profile_image = $fullPathNewImage;

        $commerce->save();

        return response()->json(['status' => 'success'], 200);

    }

    public function removeProfileImage(Request $request)
    {
        $commerceId;

        if(Auth::user()->hasRole(['ceo', 'cto', 'gabu.employee']))
        {
            $validator = Validator::make($request->all(),
            [
                'commerce_id'           =>  'required|exists:commerces,id'
            ],
            [
                'commerce_id.required'  =>  'El ID del Comercio es Requerido',
                'commerce_id.exists'    =>  'El Comercio no existe',
            ]);

            if($validator->fails())
                return response()->json(['errors' => $validator->errors()], 422);

            $commerceId = $request->commerce_id;
        }
        else
        {
            $commerceId = Auth::user()->merchant->commerce->id;
        }

        $commerce = Commerce::find($commerceId);

        $auxPath = "files/commerces/profile-image/" . $commerce->id;
        $path = public_path($auxPath);
        File::deleteDirectory($path); //Garantiza de borrar las imágenes de perfil anterior

        $commerce->original_profile_image = null;
        $commerce->thumbnail_profile_image = null;
        $commerce->avatar_profile_image = null;

        $commerce->save();

        return response()->json(['status' => 'success'], 200);
    }

    public function showContact($idPublicCommerce)
    {
        if(Commerce::where('id_public', $idPublicCommerce)->count() == 0)
            return response()->json(['error'   =>  'El Comercio no existe'], 422);

        $commerce = Commerce::where('id_public', $idPublicCommerce)->first();

        $contact = Contact::where('commerce_id', $commerce->id)
                            ->select('commerce_id as id_commerce', 'web as web_contact', 'whatsapp as whatsapp_contact', 'instagram as instagram_contact', 'facebook as facebook_contact', 'twitter as twitter_contact', 'linkedin as linkedin_contact', 'youtube as youtube_contact', 'tiktok as tiktok_contact', 'phone_1 as phone_1_contact', 'phone_2 as phone_2_contact', 'email as email_contact',)
                            ->first();

        return response()->json([
            'status' => 'success',
            'contact'  => $contact
        ], 200);
    }

    public function updateContact(Request $request, $idPublicCommerce)
    {

        $commerce = Commerce::where('id_public', $idPublicCommerce)->first();

        if(!$commerce)
            return response()->json(['error'   =>  'El Comercio no existe'], 422);

        if(Auth::user()->hasRole('commerce.owner'))
        {
            if(Auth::user()->merchant->commerce_id != $commerce->id)
                return response()->json(['error'   =>  'El Comercio no le pertenece'], 422);
        }

        $validator = Validator::make($request->all(),
        [
            'web'           =>  'url|max:128',
            'whatsapp'      =>  'string|between:4,32|unique:contacts',
            'instagram'     =>  'string|max:128',
            'facebook'      =>  'url|max:128',
            'twitter'       =>  'string|max:128',
            'linkedin'      =>  'url|max:128',
            'youtube'       =>  'url|max:128',
            'tiktok'        =>  'url|max:128',
            'phone_1'       =>  'string|between:4,32',
            'phone_2'       =>  'string|between:4,32',
            'email'         =>  'email|max:128',
        ],
        [

            'web.url'               =>  'La web no es una URL válida',
            'web.max'               =>  'La Web debe tener una longitud máxina de 128 caracteres',
            'whatsapp.string'       =>  'WhatsApp tiene un formato inválido ',
            'whatsapp.between'      =>  'La longitud del WhatsApp debe ser entre 4 y 32 caracteres',
            'whatsapp.unique'       =>  'El número de WhatsApp ya está siendo utilizado',
            'instagram.string'      =>  'Instagram tiene un formato inválido',
            'instagram.max'         =>  'Instagram debe tener una longitud máxina de 128 caracteres',
            'facebook.url'          =>  'Facebook no es una URL válida',
            'facebook.max'          =>  'Facebook debe tener una longitud máxina de 128 caracteres',
            'twitter.string'        =>  'Twitter tiene un formato inválido',
            'twitter.max'           =>  'Twitter debe tener una longitud máxina de 128 caracteres',
            'linkedin.url'          =>  'Linkedin no es una URL válida',
            'linkedin.max'          =>  'Linkedin debe tener una longitud máxina de 128 caracteres',
            'youtube.url'           =>  'Youtube no es una URL válida',
            'youtube.max'           =>  'Youtube debe tener una longitud máxina de 128 caracteres',
            'tiktok.url'            =>  'TikTok no es una URL válida',
            'tiktok.max'            =>  'TikTok debe tener una longitud máxina de 128 caracteres',
            'phone_1.string'        =>  'El Teléfono 1 tiene un formato inválido ',
            'phone_1.between'       =>  'La longitud del Teléfono 1 debe ser entre 4 y 32 caracteres',
            'phone_2.string'        =>  'El Teléfono 2 tiene un formato inválido ',
            'phone_2.between'       =>  'La longitud del Teléfono 2 debe ser entre 4 y 32 caracteres',
            'email.email'           =>  'El Correo Electrónico tiene un formato inválido',
            'email.max'             =>  'El Correo Electrónico debe tener una longitud máxina de 128 caracteres',
        ]);

        if($validator->fails())
                return response()->json(['errors' => $validator->errors()], 422);


        if($request->exists('web'))
            $commerce->contact->web = $request->web;

        if($request->exists('whatsapp'))
            $commerce->contact->whatsapp = $request->whatsapp;

        if($request->exists('instagram'))
            $commerce->contact->instagram = $request->instagram;

        if($request->exists('facebook'))
            $commerce->contact->facebook = $request->facebook;

        if($request->exists('twitter'))
            $commerce->contact->twitter = $request->twitter;

        if($request->exists('linkedin'))
            $commerce->contact->linkedin = $request->linkedin;

        if($request->exists('youtube'))
            $commerce->contact->youtube = $request->youtube;

        if($request->exists('tiktok'))
            $commerce->contact->tiktok = $request->tiktok;

        if($request->exists('phone_1'))
            $commerce->contact->phone_1 = $request->phone_1;

        if($request->exists('phone_2'))
            $commerce->contact->phone_2 = $request->phone_2;

        if($request->exists('email'))
            $commerce->contact->email = $request->email;

        if($commerce->contact->save())
            return response()->json(['status' => 'success'], 200);

        return response()->json(['error'   => 'No se pudo actualizar el Contacto del Comercio']);

    }

    public function accountSetting() {
        if(Auth::user()->hasRole(['commerce.owner', 'commerce.employee'])) {
            $commerce = Commerce::leftJoin('contacts', 'contacts.commerce_id', '=', 'commerces.id')
                                    ->where('commerces.id', Auth::user()->merchant->commerce_id)
                                    ->select('commerces.id as id', 'commerces.id_public as id_public', 'commerces.trade_name as trade_name', 'commerces.legal_name as legal_name', 'commerces.thumbnail_profile_image as thumbnail_image', 'commerces.tax_identification_number as tax_number', 'commerces.slogan as slogan', 'commerces.short_description as short_description', DB::raw("DATE_FORMAT(commerces.created_at, '%d-%m-%Y') as registration_date"), 'commerces.address as address', 'contacts.id as id_contact', 'contacts.whatsapp as whatsapp', 'contacts.web as web', 'contacts.phone_1 as phone_1', 'contacts.instagram as instagram', 'contacts.facebook as facebook')
                                    ->first();

            return response()->json([
                'status'    => 'success',
                'commerce'  =>  $commerce,
            ], 200);
        }

        return response()->json(['error' => 'No es un Comerciante'], 422);
    }

}



