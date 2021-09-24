<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Commerce;
use App\Models\Employee;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use jeremykenedy\LaravelRoles\Models\Role;
use Illuminate\Support\Str;
use \Gumlet\ImageResize;

class OfferController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $offers = Offer::leftJoin('merchants', 'merchants.id', '=', 'offers.commerce_id')
                            ->leftJoin('commerces', 'commerces.id', '=', 'offers.commerce_id')
                            ->leftJoin('employees', 'employees.id', '=', 'offers.employee_id');

        if($request->exists('commerce_id_public'))
        {
            $commerce = Commerce::where('id_public', $request->commerce_id_public)->first();
            if($commerce)
                $offers = $offers->where('offers.commerce_id', $commerce->id);
            else
                $offers = $offers->where('offers.commerce_id', null);
        }



        if(Auth::user()->hasRole(['ceo', 'cto', 'wolof.employee']))
        {
            if($request->exists('employee_id_public'))
            {
                $employee = Employee::where('id_public', $request->employee_id_public)->first();
                $offers = $offers->where('offers.employee_id', $employee->id);
            }

            if($request->exists('merchant_id_public'))
            {
                $merchant = Merchant::where('id_public', $request->merchant_id_public)->first();
                $offers = $offers->where('offers.merchant_id', $merchant->id);
            }
        }

        if($request->exists('order_by'))
        {
            if(in_array($request->order_by, ['created_at_asc', 'created_at_desc', 'price_asc', 'price_desc']))
            {
                switch($request->order_by)
                {
                    case 'created_at_asc':      $offers = $offers->orderBy('offers.created_at', 'asc');
                                                break;

                    case 'created_at_desc':     $offers = $offers->orderBy('offers.created_at', 'desc');
                                                break;

                    case 'price_asc':           $offers = $offers->orderBy('offers.price', 'asc');
                                                break;

                    case 'price_desc':          $offers = $offers->orderBy('offers.price', 'desc');
                                                break;
                }
            }
        }

        if(Auth::user()->hasRole(['ceo', 'cto', 'wolof.employee', 'commerce.owner', 'commerce.employee']))
        {
            if($request->exists('status'))
            {
                if(in_array($request->status, ['active', 'suspended']))
                    $offers = $offers->where('offers.status', $request->status);
            }

            if(Auth::user()->hasRole(['commerce.owner', 'commerce.employee']))
                $offers = $offers->where('offers.commerce_id', Auth::user()->merchant->commerce->id);

            $offers = $offers->select('offers.id as id', 'commerces.id as id_commerce', 'commerces.id_public as id_public_commerce', 'commerces.trade_name as trade_name_commerce', 'merchants.id as id_merchant', 'merchants.id_public as id_public_merchant', 'merchants.name as name_merchant', 'merchants.surname as surname_merchant', 'employees.id as id_employee', 'employees.id_public as id_public_employee', 'employees.full_name as fullname_employee', 'offers.title as title ', 'offers.description as description', 'offers.status as status', 'offers.price as price', 'offers.sales as sales', 'offers.original_image as original_image', 'offers.thumbnail_image as thumbnail_image', 'offers.avatar_image as avatar_image', 'offers.created_at as created_at', 'offers.updated_at as updated_at')
                                ->get();

        }
        else
        {
            $offers = $offers->where('offers.status', 'active');

            $offers = $offers->select('offers.id as id', 'commerces.id as id_commerce', 'commerces.id_public as id_public_commerce', 'commerces.trade_name as trade_name_commerce', 'offers.title as title ', 'offers.description as description', 'offers.price as price', 'offers.sales as sales', 'offers.original_image as original_image', 'offers.thumbnail_image as thumbnail_image', 'offers.avatar_image as avatar_image')
                                ->get();

        }

        return response()->json(
            [
                'status'    =>  'success',
                'offers'    =>  $offers
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
            'commerce_id_public'       =>  'exists:commerces,id_public',
            'title'             =>  'required|max:64',
            'description'       =>  'max:128',
            'status'            =>  'in:"active","suspended"',
            'price'             =>  'required|numeric',
            'image'             =>  'required|file|max:3072|dimensions:min_width=300,max_width=3200,min_height=300,max_height=3200|mimes:jpeg,bmp,png'
        ],
        [
            'commerce_id_public.exists'    =>  'El Comercio no existe',
            'title.required'        =>  'El Título es requerido',
            'title.max'             =>  'El Título no debe ser mayor a 64 caracteres',
            'description.max'       =>  'La Descripción no debe ser mayor a 64 caracteres',
            'status.in'             =>  'Los valores de Estatus deben ser active o suspended',
            'price.required'        =>  'El Precio es requerido',
            'price.numeric'         =>  'El Precio debe ser numérico',
            'image.required'        =>  'La Imagen es requerida',
            'image.file'            =>  'La Imagen debe ser un tipo de archivo',
            'image.max'             =>  'La Imagen debe tener un peso máximo de 3MB',
            'image.dimensions'      =>  'El tamaño de la Imagen debe estar entre 300px y 3200px',
            'image.mimes'           =>  'La Imagen debe ser jpg, bmp o png'
        ]);

        if($validator->fails())
            return response()->json(['errors'   =>  $validator->errors()], 422);

        $offer = new Offer();
        $commerce;

        if(Auth::user()->hasRole(['ceo', 'cto', 'wolof.employee']))
        {
            if(!$request->exists('commerce_id_public'))
                return response()->json(['errors'   =>  'El ID del Comercio es requerdio'], 422);

            $commerce = Commerce::where('id_public', $request->commerce_id_public)->first();
            $offer->commerce_id = $commerce->id;

            $offer->employee_id = Auth::user()->employee->id;
        }
        else{
            $offer->commerce_id = Auth::user()->merchant->commerce->id;

            $offer->merchant_id = Auth::user()->merchant->id;

            $commerce = Commerce::find($offer->commerce_id);
        }

        $offer->title = $request->title;

        if($request->exists('description'))
            $offer->description = $request->description;

        if($request->exists('status'))
            $offer->status = $request->status;

        $offer->price = $request->price;

        if(!$offer->save())
            return response()->json(['errors' => 'No se pudo guardar la Oferta'], 422);

        $auxPath = "files/offers/" . $commerce->id_public . "/" . $offer->id;
        $path = public_path($auxPath);
        Storage::makeDirectory($path);

        //original_profile_image
        $auxIMG = $request->image;
        $extension = $auxIMG->extension();
        $originalName = str_replace(' ','', $auxIMG->getClientOriginalName());
        $auxIMG->move($path, $originalName);

        $fullPathOriginalImage = $auxPath . '/' . $originalName;
        $offer->original_image = $auxPath . '/' . $originalName;

        $fullPathNewImage = $auxPath . '/' . Str::random(12) . '.' . $extension;
        $image = new ImageResize($fullPathOriginalImage);
        $image->resizeToHeight(640);
        $image->save($fullPathNewImage);
        $offer->thumbnail_image = $fullPathNewImage;

        $fullPathNewImage = $auxPath . '/' . Str::random(12) . '.' . $extension;
        $image = new ImageResize($fullPathOriginalImage);
        $image->resizeToHeight(180);
        $image->save($fullPathNewImage);
        $offer->avatar_image = $fullPathNewImage;

        if($offer->save())
            return response()->json(['status'    =>  'success'], 200);

        File::deleteDirectory($path);

        return response()->json(['errors' => 'No se pudo guardar la Oferta'], 422);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Offer  $offer
     * @return \Illuminate\Http\Response
     */
    public function show($idOffer)
    {
        if(Offer::where('id', $idOffer)->count() == 0)
            return response()->json(['errors'   =>  'La Oferta no existe'], 422);

        $offer = Offer::leftJoin('merchants', 'merchants.id', '=', 'offers.commerce_id')
                            ->leftJoin('commerces', 'commerces.id', '=', 'offers.commerce_id')
                            ->leftJoin('employees', 'employees.id', '=', 'offers.employee_id')
                            ->where('offers.id', $idOffer);

        if(Auth::user()->hasRole(['ceo', 'cto', 'wolof.employee', 'commerce.owner', 'commerce.employee']))
        {
            if(Auth::user()->hasRole(['commerce.owner', 'commerce.employee']))
                $offer = $offer->where('offers.commerce_id', Auth::user()->merchant->commerce->id);

            $offer = $offer->select('offers.id as id', 'commerces.id as id_commerce', 'commerces.id_public as id_public_commerce', 'commerces.trade_name as trade_name_commerce', 'merchants.id as id_merchant', 'merchants.id_public as id_public_merchant', 'merchants.name as name_merchant', 'merchants.surname as surname_merchant', 'employees.id as id_employee', 'employees.id_public as id_public_employee', 'employees.full_name as fullname_employee', 'offers.title as title ', 'offers.description as description', 'offers.status as status', 'offers.price as price', 'offers.sales as sales', 'offers.original_image as original_image', 'offers.thumbnail_image as thumbnail_image', 'offers.avatar_image as avatar_image', 'offers.created_at as created_at', 'offers.updated_at as updated_at')
                            ->first();
        }
        else
        {
            $offer = $offer->where('offers.status', 'active');
            $offer = $offer->select('offers.id as id', 'commerces.id as id_commerce', 'commerces.id_public as id_public_commerce', 'commerces.trade_name as trade_name_commerce', 'offers.title as title ', 'offers.description as description', 'offers.price as price', 'offers.sales as sales', 'offers.original_image as original_image', 'offers.thumbnail_image as thumbnail_image', 'offers.avatar_image as avatar_image')
                            ->first();
        }

        return response()->json(
        [
            'status'    =>  'success',
            'offer'    =>  $offer
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Offer  $offer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $idOffer)
    {

        $offer = Offer::find($idOffer);

        if(!$offer)
            return response()->json(['errors'   =>  'La Oferta no existe'], 422);

        $validator = Validator::make($request->all(),
        [
            'title'             =>  'max:64',
            'description'       =>  'max:128',
            'status'            =>  'in:"active","suspended"',
            'price'             =>  'numeric',
        ],
        [
            'title.max'             =>  'El Título no debe ser mayor a 64 caracteres',
            'description.max'       =>  'La Descripción no debe ser mayor a 64 caracteres',
            'status.in'             =>  'Los valores de Estatus deben ser active o suspended',
            'price.numeric'         =>  'El Precio debe ser numérico',
        ]);

        if($validator->fails())
            return response()->json(['errors'   =>  $validator->errors()], 422);

        if(Auth::user()->hasRole(['ceo', 'cto', 'wolof.employee']))
        {
            $offer->employee_id = Auth::user()->employee->id;
            $offer->merchant_id = null;
        }
        else
        {
            if($offer->commerce_id != Auth::user()->merchant->commerce->id)
                return response()->json(['errors'   =>  'Usted no pertenece al Comercio'], 422);

            $offer->employee_id = null;

            $offer->merchant_id = Auth::user()->merchant->id;
        }

        if($request->exists('title'))
            $offer->title = $request->title;

        if($request->exists('description'))
            $offer->description = $request->description;

        if($request->exists('status'))
            $offer->status = $request->status;

        if($request->exists('price'))
            $offer->price = $request->price;

        if($offer->save())
            return response()->json(['status'    =>  'success'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Offer  $offer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Offer $offer)
    {
        // TODO: Solo se puede borrar si nadie ha realizado una compra del mismo
    }


    public function updateImage(Request $request, $idOffer)
    {
        $offer = Offer::find($idOffer);

        if(!$offer)
            return response()->json(['errors'   =>  'La Oferta no existe'], 422);

        $validator = Validator::make($request->all(),
        [
            'commerce_id'       =>  'exists:commerces,public_id',
            'image'             =>  'required|file|max:3072|dimensions:min_width=300,max_width=3200,min_height=300,max_height=3200|mimes:jpeg,bmp,png'
        ],
        [
            'commerce_id.exists'    =>  'El Comercio no existe',
            'image.required'        =>  'La Imagen es requerida',
            'image.file'            =>  'La Imagen debe ser un tipo de archivo',
            'image.max'             =>  'La Imagen debe tener un peso máximo de 3MB',
            'image.dimensions'      =>  'El tamaño de la Imagen debe estar entre 300px y 3200px',
            'image.mimes'           =>  'La Imagen debe ser jpg, bmp o png'
        ]);

        if($validator->fails())
            return response()->json(['errors'   =>  $validator->errors()], 422);

        $commerce = Commerce::find($offer->commerce_id);

        if(Auth::user()->hasRole(['commerce.owner', 'commerce.employee']))
        {
            if($offer->commerce_id != Auth::user()->merchant->commerce->id)
                return response()->json(['errors'   =>  'Usted no pertenece al Comercio'], 422);
        }


        $auxPath = "files/offers/" . $commerce->id_public . "/" . $offer->id;
        $path = public_path($auxPath);
        File::deleteDirectory($path); //Garantiza de borrar las imágenes anteriores
        Storage::makeDirectory($path);

        //original_profile_image
        $auxIMG = $request->image;
        $extension = $auxIMG->extension();
        $originalName = str_replace(' ','', $auxIMG->getClientOriginalName());
        $auxIMG->move($path, $originalName);

        $fullPathOriginalImage = $auxPath . '/' . $originalName;
        $offer->original_image = $auxPath . '/' . $originalName;

        $fullPathNewImage = $auxPath . '/' . Str::random(12) . '.' . $extension;
        $image = new ImageResize($fullPathOriginalImage);
        $image->resize(640, 640);
        $image->save($fullPathNewImage);
        $offer->thumbnail_image = $fullPathNewImage;

        $fullPathNewImage = $auxPath . '/' . Str::random(12) . '.' . $extension;
        $image = new ImageResize($fullPathOriginalImage);
        $image->resize(180, 180);
        $image->save($fullPathNewImage);
        $offer->avatar_image = $fullPathNewImage;

        $offer->save();

        return response()->json(['status'    =>  'success'], 200);

    }
}
