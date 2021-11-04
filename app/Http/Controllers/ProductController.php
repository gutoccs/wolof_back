<?php

namespace App\Http\Controllers;

use App\Models\Product;
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

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $products = Product::leftJoin('merchants', 'merchants.id', '=', 'products.commerce_id')
                            ->leftJoin('commerces', 'commerces.id', '=', 'products.commerce_id')
                            ->leftJoin('employees', 'employees.id', '=', 'products.employee_id');

        if($request->exists('id'))
        {
            $products = $products->where('products.id', $request->id);
        }

        if($request->exists('commerce_id_public'))
        {
            $commerce = Commerce::where('id_public', $request->commerce_id_public)->first();
            if($commerce)
                $products = $products->where('products.commerce_id', $commerce->id);
            else
                $products = $products->where('products.commerce_id', null);
        }



        if(Auth::user()->hasRole(['ceo', 'cto', 'gabu.employee']))
        {
            if($request->exists('employee_id_public'))
            {
                $employee = Employee::where('id_public', $request->employee_id_public)->first();
                $products = $products->where('products.employee_id', $employee->id);
            }

            if($request->exists('merchant_id_public'))
            {
                $merchant = Merchant::where('id_public', $request->merchant_id_public)->first();
                $products = $products->where('products.merchant_id', $merchant->id);
            }
        }

        if($request->exists('order_by'))
        {
            if(in_array($request->order_by, ['created_at_asc', 'created_at_desc', 'price_asc', 'price_desc', 'quantity_available_asc', 'quantity_available_desc']))
            {
                switch($request->order_by)
                {
                    case 'created_at_asc':      $products = $products->orderBy('products.created_at', 'asc');
                                                break;

                    case 'created_at_desc':     $products = $products->orderBy('products.created_at', 'desc');
                                                break;

                    case 'price_asc':           $products = $products->orderBy('products.price', 'asc');
                                                break;

                    case 'price_desc':          $products = $products->orderBy('products.price', 'desc');
                                                break;

                    case 'quantity_available_asc':    $products = $products->orderBy('products.quantity_available', 'asc');
                                                break;

                    case 'quantity_available_desc':   $products = $products->orderBy('products.quantity_available', 'desc');
                                                break;
                }
            }
        }

        if(Auth::user()->hasRole(['ceo', 'cto', 'gabu.employee', 'commerce.owner', 'commerce.employee']))
        {
            if($request->exists('status'))
            {
                if(in_array($request->status, ['active', 'suspended']))
                    $products = $products->where('products.status', $request->status);
            }

            if(Auth::user()->hasRole(['commerce.owner', 'commerce.employee']))
                $products = $products->where('products.commerce_id', Auth::user()->merchant->commerce->id);

            $products = $products->select('products.id as id', 'commerces.id as id_commerce', 'commerces.id_public as id_public_commerce', 'commerces.trade_name as trade_name_commerce', 'merchants.id as id_merchant', 'merchants.id_public as id_public_merchant', 'merchants.name as name_merchant', 'merchants.surname as surname_merchant', 'employees.id as id_employee', 'employees.id_public as id_public_employee', 'employees.full_name as fullname_employee', 'products.title as title ', 'products.description as description', 'products.status as status', 'products.quantity_available as quantity_available', 'products.price as price', 'products.sales as sales', 'products.original_image as original_image', 'products.thumbnail_image as thumbnail_image', 'products.avatar_image as avatar_image', 'products.created_at as created_at', 'products.updated_at as updated_at')
                                ->get();

        }
        else
        {
            $products = $products->where('products.status', 'active')
                                    ->where('products.quantity_available', '>', 0);

            $products = $products->select('products.id as id', 'commerces.id as id_commerce', 'commerces.id_public as id_public_commerce', 'commerces.trade_name as trade_name_commerce', 'products.title as title ', 'products.description as description', 'products.quantity_available as quantity_available', 'products.price as price', 'products.sales as sales', 'products.original_image as original_image', 'products.thumbnail_image as thumbnail_image', 'products.avatar_image as avatar_image', 'products.created_at as created_at', 'products.updated_at as updated_at')
                                ->get();

        }


        return response()->json(
            [
                'status'    =>  'success',
                'products'    =>  $products
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
            'commerce_id_public'    =>  'exists:commerces,id_public',
            'title'                 =>  'required|max:64',
            'description'           =>  'max:128',
            'status'                =>  'in:"active","suspended"',
            'quantity_available'    =>  'required|numeric|min:0',
            'price'                 =>  'required|numeric',
            'image'                 =>  'required|file|max:5120|dimensions:min_width=300,max_width=4000,min_height=300,max_height=4000|mimes:jpeg,bmp,png'
        ],
        [
            'commerce_id_public.exists'     =>  'El Comercio no existe',
            'title.required'                =>  'El Nombre es requerido',
            'title.max'                     =>  'El Nombre no debe ser mayor a 64 caracteres',
            'description.max'               =>  'La Descripción no debe ser mayor a 64 caracteres',
            'status.in'                     =>  'El valor de Producto Activo es erróneo',
            'quantity_available.required'   =>  'La Cantidad Disponible es requerida',
            'quantity_available.numeric'    =>  'La Cantidad Disponible debe ser numérico',
            'quantity_available.min'        =>  'La Cantidad Disponible debe ser mínimo 0',
            'price.required'                =>  'El Precio es requerido',
            'price.numeric'                 =>  'El Precio debe ser numérico',
            'image.required'                =>  'La Imagen es requerida',
            'image.file'                    =>  'La Imagen debe ser un tipo de archivo',
            'image.max'                     =>  'La Imagen debe tener un peso máximo de 5MB',
            'image.dimensions'              =>  'El tamaño de la Imagen debe estar entre 300px y 4000px',
            'image.mimes'                   =>  'La Imagen debe ser jpg, bmp o png'
        ]);

        if($validator->fails())
            return response()->json(['errors'   =>  $validator->errors()], 422);

        $product = new Product();
        $commerce;

        if(Auth::user()->hasRole(['ceo', 'cto', 'gabu.employee']))
        {
            if(!$request->exists('commerce_id_public'))
                return response()->json(['error'   =>  'El ID del Comercio es requerdio'], 422);

            $commerce = Commerce::where('id_public', $request->commerce_id_public)->first();
            $product->commerce_id = $commerce->id;

            $product->employee_id = Auth::user()->employee->id;
        }
        else{
            $product->commerce_id = Auth::user()->merchant->commerce->id;

            $product->merchant_id = Auth::user()->merchant->id;

            $commerce = Commerce::find($product->commerce_id);
        }

        $product->title = $request->title;

        if($request->exists('description'))
            $product->description = $request->description;


        if($request->exists('status'))
            $product->status = $request->status;


        $product->quantity_available = $request->quantity_available;

        if($product->quantity_available == 0)
            $product->status = 'suspended';

        $product->price = $request->price;

        if(!$product->save())
            return response()->json(['error' => 'No se pudo guardar el Producto'], 422);

        $auxPath = "files/products/" . $commerce->id_public . "/" . $product->id;
        $path = public_path($auxPath);
        Storage::makeDirectory($path);

        //original_profile_image
        $auxIMG = $request->image;
        $extension = $auxIMG->extension();
        $originalName = str_replace(' ','', $auxIMG->getClientOriginalName());
        $auxIMG->move($path, $originalName);

        $fullPathOriginalImage = $auxPath . '/' . $originalName;
        $product->original_image = $auxPath . '/' . $originalName;

        $fullPathNewImage = $auxPath . '/' . Str::random(12) . '.' . $extension;
        $image = new ImageResize($fullPathOriginalImage);
        $image->resizeToHeight(640);
        $image->save($fullPathNewImage);
        $product->thumbnail_image = $fullPathNewImage;

        $fullPathNewImage = $auxPath . '/' . Str::random(12) . '.' . $extension;
        $image = new ImageResize($fullPathOriginalImage);
        $image->resizeToHeight(180);
        $image->save($fullPathNewImage);
        $product->avatar_image = $fullPathNewImage;

        if($product->save())
            return response()->json(['status'    =>  'success'], 200);

        File::deleteDirectory($path);

        return response()->json(['error' => 'No se pudo guardar el Producto'], 422);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show($idProduct)
    {
        if(Product::where('id', $idProduct)->count() == 0)
            return response()->json(['error'   =>  'El Producto no existe'], 422);

        $product = Product::leftJoin('merchants', 'merchants.id', '=', 'products.commerce_id')
                            ->leftJoin('commerces', 'commerces.id', '=', 'products.commerce_id')
                            ->leftJoin('employees', 'employees.id', '=', 'products.employee_id')
                            ->where('products.id', $idProduct);

        if(Auth::user()->hasRole(['ceo', 'cto', 'gabu.employee', 'commerce.owner', 'commerce.employee']))
        {
            if(Auth::user()->hasRole(['commerce.owner', 'commerce.employee']))
                $product = $product->where('products.commerce_id', Auth::user()->merchant->commerce->id);

            $product = $product->select('products.id as id', 'commerces.id as id_commerce', 'commerces.id_public as id_public_commerce', 'commerces.trade_name as trade_name_commerce', 'merchants.id as id_merchant', 'merchants.id_public as id_public_merchant', 'merchants.name as name_merchant', 'merchants.surname as surname_merchant', 'employees.id as id_employee', 'employees.id_public as id_public_employee', 'employees.full_name as fullname_employee', 'products.title as title ', 'products.description as description', 'products.status as status', 'products.quantity_available as quantity_available', 'products.price as price', 'products.sales as sales', 'products.original_image as original_image', 'products.thumbnail_image as thumbnail_image', 'products.avatar_image as avatar_image', 'products.created_at as created_at', 'products.updated_at as updated_at')
                            ->first();
        }
        else
        {
            $product = $product->where('products.status', 'active')
                                ->where('products.quantity_available', '>', 0);
            $product = $product->select('products.id as id', 'commerces.id as id_commerce', 'commerces.id_public as id_public_commerce', 'commerces.trade_name as trade_name_commerce', 'products.title as title ', 'products.description as description', 'products.quantity_available as quantity_available', 'products.price as price', 'products.sales as sales', 'products.original_image as original_image', 'products.thumbnail_image as thumbnail_image', 'products.avatar_image as avatar_image', 'products.created_at as created_at', 'products.updated_at as updated_at')
                            ->first();
        }

        return response()->json(
        [
            'status'    =>  'success',
            'product'    =>  $product
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $idProduct)
    {

        $product = Product::find($idProduct);

        if(!$product)
            return response()->json(['error'   =>  'El Producto no existe'], 422);

        $validator = Validator::make($request->all(),
        [
            'title'                 =>  'max:64',
            'description'           =>  'max:128',
            'status'                =>  'in:"active","suspended"',
            'quantity_available'    =>  'required|numeric|min:0',
            'price'                 =>  'numeric',
        ],
        [
            'title.max'                     =>  'El Nombre no debe ser mayor a 64 caracteres',
            'description.max'               =>  'La Descripción no debe ser mayor a 64 caracteres',
            'status.in'                     =>  'El valor de Producto Activo es erróneo',
            'quantity_available.required'   =>  'La Cantidad Disponible es requerida',
            'quantity_available.numeric'    =>  'La Cantidad Disponible debe ser numérico',
            'quantity_available.min'        =>  'La Cantidad Disponible debe ser mínimo 0',
            'price.numeric'                 =>  'El Precio debe ser numérico',
        ]);

        if($validator->fails())
            return response()->json(['errors'   =>  $validator->errors()], 422);

        if(Auth::user()->hasRole(['ceo', 'cto', 'gabu.employee']))
        {
            $product->employee_id = Auth::user()->employee->id;
            $product->merchant_id = null;
        }
        else
        {
            if($product->commerce_id != Auth::user()->merchant->commerce->id)
                return response()->json(['error'   =>  'Usted no pertenece al Comercio'], 422);

            $product->employee_id = null;

            $product->merchant_id = Auth::user()->merchant->id;
        }

        if($request->exists('title'))
            $product->title = $request->title;

        if($request->exists('description'))
            $product->description = $request->description;

        if($request->exists('status'))
            $product->status = $request->status;

        if($request->exists('quantity_available'))
            $product->quantity_available = $request->quantity_available;

        if($product->quantity_available == 0)
            $product->status = 'suspended';


        if($request->exists('price'))
            $product->price = $request->price;

        if($product->save())
            return response()->json(['status'    =>  'success'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($idProduct)
    {
        // TODO: Solo se puede borrar si nadie ha realizado una compra del mismo
    }


    public function updateImage(Request $request, $idProduct)
    {
        $product = Product::find($idProduct);

        if(!$product)
            return response()->json(['error'   =>  'El Producto no existe'], 422);

        $validator = Validator::make($request->all(),
        [
            'commerce_id'       =>  'exists:commerces,public_id',
            'image'             =>  'required|file|max:5120|dimensions:min_width=300,max_width=4000,min_height=300,max_height=4000|mimes:jpeg,bmp,png'
        ],
        [
            'commerce_id.exists'    =>  'El Comercio no existe',
            'image.required'        =>  'La Imagen es requerida',
            'image.file'            =>  'La Imagen debe ser un tipo de archivo',
            'image.max'             =>  'La Imagen debe tener un peso máximo de 5MB',
            'image.dimensions'      =>  'El tamaño de la Imagen debe estar entre 300px y 4000px',
            'image.mimes'           =>  'La Imagen debe ser jpg, bmp o png'
        ]);

        if($validator->fails())
            return response()->json(['errors'   =>  $validator->errors()], 422);

        $commerce = Commerce::find($product->commerce_id);

        if(Auth::user()->hasRole(['commerce.owner', 'commerce.employee']))
        {
            if($product->commerce_id != Auth::user()->merchant->commerce->id)
                return response()->json(['error'   =>  'Usted no pertenece al Comercio'], 422);
        }


        $auxPath = "files/products/" . $commerce->id_public . "/" . $product->id;
        $path = public_path($auxPath);
        File::deleteDirectory($path); //Garantiza de borrar las imágenes anteriores
        Storage::makeDirectory($path);

        //original_profile_image
        $auxIMG = $request->image;
        $extension = $auxIMG->extension();
        $originalName = str_replace(' ','', $auxIMG->getClientOriginalName());
        $auxIMG->move($path, $originalName);

        $fullPathOriginalImage = $auxPath . '/' . $originalName;
        $product->original_image = $auxPath . '/' . $originalName;

        $fullPathNewImage = $auxPath . '/' . Str::random(12) . '.' . $extension;
        $image = new ImageResize($fullPathOriginalImage);
        $image->resize(640, 640);
        $image->save($fullPathNewImage);
        $product->thumbnail_image = $fullPathNewImage;

        $fullPathNewImage = $auxPath . '/' . Str::random(12) . '.' . $extension;
        $image = new ImageResize($fullPathOriginalImage);
        $image->resize(180, 180);
        $image->save($fullPathNewImage);
        $product->avatar_image = $fullPathNewImage;

        $product->save();

        return response()->json(['status'    =>  'success'], 200);

    }
}
