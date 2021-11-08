<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Merchant;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $purchases = Purchase::leftJoin('products', 'products.id', '=', 'purchases.product_id')
                                ->leftJoin('clients', 'clients.id', '=', 'purchases.client_id')
                                ->leftJoin('employees', 'employees.id', '=', 'purchases.employee_id')
                                ->leftJoin('merchants', 'merchants.id', '=', 'purchases.merchant_id')
                                ->leftJoin('commerces', 'commerces.id', '=', 'purchases.commerce_id');


        if(Auth::user()->hasRole(['commerce.owner', 'commerce.employee']))
        {
            $purchases = $purchases->where('purchases.commerce_id', Auth::user()->merchant->commerce->id);
        }

        if(Auth::user()->hasRole('client'))
        {
            $purchases = $purchases->where('purchases.client_id', Auth::user()->client->id);
        }

        if($request->exists('product_id'))
            $purchases = $purchases->where('purchases.product_id', $request->product_id);

        if($request->exists('client_id'))
            $purchases = $purchases->where('purchases.client_id', $request->client_id);

        if($request->exists('status'))
            $purchases = $purchases->where('purchases.status', $request->status);

        if($request->exists('client_completed'))
            $purchases = $purchases->where('purchases.client_completed', $request->client_completed);

        if($request->exists('commerce_completed'))
            $purchases = $purchases->where('purchases.commerce_completed', $request->commerce_completed);

        if($request->exists('order_by'))
        {
            if(in_array($request->order_by, ['created_at_asc', 'created_at_desc']))
            {
                switch($request->order_by)
                {
                    case 'created_at_asc':    $purchases = $purchases->orderBy('purchases.created_at', 'asc');
                                break;

                    case 'created_at_desc':    $purchases = $purchases->orderBy('purchases.created_at', 'desc');
                                break;
                }
            }

        }

        $purchases = $purchases->select('purchases.id as id', 'products.id as id_product', 'products.title as title_product', 'products.thumbnail_image as thumbnail_image_product','clients.id as id_client', 'clients.name as name_client', 'clients.surname as surname_client', 'commerces.id as id_commerce', 'commerces.trade_name as trade_name_commerce', 'purchases.amount as amount', 'purchases.status as status', 'purchases.total_to_pay as total_to_pay', 'purchases.client_completed as client_completed', 'purchases.commerce_completed as commerce_completed', 'purchases.who_canceled as who_canceled', 'purchases.reason_for_cancellation as reason_for_cancellation', 'merchants.id as id_merchant', 'merchants.name as name_merchant', 'merchants.surname as surname_merchant', 'employees.id as id_employee', 'employees.full_name as full_name_employee', 'purchases.cancelled_at as cancelled_at', 'purchases.created_at as created_at', 'purchases.updated_at as updated_at',)
                                ->orderBy('purchases.id', 'asc');

        $auxPurchases = [];
        $purchases1 = $purchases->where('purchases.status', 'active')->get();

        for($i = 0; $i < count($purchases1); $i++)
            array_push($auxPurchases, $purchases1[$i]);



        $purchases2 = $purchases->where('purchases.status', 'completed')->get();

        for($i = 0; $i < count($purchases2); $i++)
            array_push($auxPurchases, $purchases2[$i]);


        $purchases3 = $purchases->where('purchases.status', 'cancelled')->get();

        for($i = 0; $i < count($purchases3); $i++)
            array_push($auxPurchases, $purchases3[$i]);




        return response()->json(
            [
                'status'        =>  'success',
                'purchases'     =>  $auxPurchases
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
            'product_id'    =>  'required|exists:products,id',
            'amount'        =>  'required|numeric|min:1|max:3',
        ],
        [
            'product_id.required'   =>  'El ID del Producto es Requerido',
            'product_id.exists'     =>  'El Producto no existe',
            'amount.required'       =>  'La Cantidad es Requerida',
            'amount.numeric'        =>  'La Cantidad debe ser numérico',
            'amount.min'            =>  'La Cantidad mínima es 1',
            'amount.max'            =>  'La Cantidad máxima es 3',
        ]);

        if($validator->fails())
            return response()->json(['errors'   => $validator->errors()], 422);

        $product = Product::find($request->product_id);

        if($product->status == 'suspended')
            return response()->json(['error'   =>  'El Producto está Suspendido'], 422);

        if($product->quantity_available < $request->amount)
            return response()->json(['error'   =>  'No hay disponibilidad de productos para esta Compra'], 422);

        if(Purchase::where('client_id', Auth::user()->client->id)->where('status', 'active')->count() == 2)
            return response()->json(['error'   =>  'Antes de volver a comprar debe completar sus dos compras activas'], 422);

        $purchase = new Purchase();

        $purchase->product_id = $product->id;
        $purchase->client_id = Auth::user()->client->id;
        $purchase->commerce_id = $product->commerce_id;
        $purchase->amount = $request->amount;
        $purchase->total_to_pay = $product->price * $request->amount;

        if($purchase->save())
        {
            $product->quantity_available = $product->quantity_available - $purchase->amount;
            $product->sales = $product->sales + $purchase->amount;
            if($product->quantity_available == 0)
                $product->status = 'suspended';

            $product->save();

            return response()->json(['status'    =>  'success'], 200);
        }

        return response()->json(['error' => 'No se pudo guardar la Compra'], 422);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function show($idPurchase)
    {
        if(Purchase::where('id', $idPurchase)->count() == 0)
            return response()->json(['error'   =>  'La Compra no existe'], 422);

        $purchase = Purchase::leftJoin('products', 'products.id', '=', 'purchases.product_id')
                                ->leftJoin('clients', 'clients.id', '=', 'purchases.client_id')
                                ->leftJoin('employees', 'employees.id', '=', 'purchases.employee_id')
                                ->leftJoin('merchants', 'merchants.id', '=', 'purchases.merchant_id')
                                ->leftJoin('commerces', 'commerces.id', '=', 'purchases.commerce_id')
                                ->where('purchases.id', $idPurchase);


        if(Auth::user()->hasRole(['commerce.owner', 'commerce.employee']))
        {
            $purchase = $purchase->where('purchases.commerce_id', Auth::user()->merchant->commerce->id);
        }

        if(Auth::user()->hasRole('client'))
        {
            $purchase = $purchase->where('purchases.client_id', Auth::user()->client->id);
        }

        $purchase = $purchase->select('purchases.id as id', 'products.id as id_product', 'products.title as title_product', 'products.thumbnail_image as thumbnail_image_product', 'clients.id as id_client', 'clients.name as name_client', 'clients.surname as surname_client', 'commerces.id as id_commerce', 'commerces.trade_name as trade_name_commerce', 'purchases.amount as amount', 'purchases.status as status', 'purchases.total_to_pay as total_to_pay', 'purchases.client_completed as client_completed', 'purchases.commerce_completed as commerce_completed', 'purchases.who_canceled as who_canceled', 'purchases.reason_for_cancellation as reason_for_cancellation', 'merchants.id as id_merchant', 'merchants.name as name_merchant', 'merchants.surname as surname_merchant', 'employees.id as id_employee', 'employees.full_name as full_name_employee', 'purchases.cancelled_at as cancelled_at', 'purchases.created_at as created_at', 'purchases.updated_at as updated_at',)
                                ->first();

        return response()->json(
        [
            'status'       =>  'success',
            'purchase'     =>  $purchase
        ], 200);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $idPurchase)
    {
        // No se permite edición, ya que tanto el cliente como los comerciantes pueden cancelar la compra
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Purchase  $purchase
     * @return \Illuminate\Http\Response
     */
    public function destroy($idPurchase)
    {
        // No se permite eliminar, ya que tanto el cliente como los comerciantes pueden cancelar la compra
    }

    public function cancelPurchase(Request $request, $idPurchase)
    {
        /**
         * employee_id y merchant_id será quienes cancelaron la compra
         */

        $purchase = Purchase::find($idPurchase);

        if(!$purchase)
            return response()->json(['error'   =>  'La Compra no existe'], 422);

        $validator = Validator::make($request->all(),
        [
            'reason_for_cancellation'   => 'required|max:255'
        ],
        [
            'reason_for_cancellation.required'  =>  'La Razón de Cancelación es requerida',
            'reason_for_cancellation.max'       =>  'La Razón de Cancelación no puede pasar los 255 caracteres',
        ]);

        if($validator->fails())
            return response()->json(['errors' => $validator->errors()], 422);

        if(Auth::user()->hasRole(['commerce.owner', 'commerce.employee']))
        {
            if(Auth::user()->merchant->commerce->id != $purchase->commerce_id)
                return response()->json(['error'   =>  'La Venta no pertenece a su Comercio'], 422);
        }

        if(Auth::user()->hasRole(['client']))
        {
            if($purchase->client_id != Auth::user()->client->id)
                return response()->json(['error'   =>  'La Compra no le pertenece'], 422);
        }

        if($purchase->status == 'cancelled')
            return response()->json(['error'   =>  'Esta Compra ya fue Cancelada'], 422);

        if($purchase->status == 'completed')
            return response()->json(['error'   =>  'Esta Compra ya fue Completada'], 422);


        $purchase->status = 'cancelled';
        $purchase->reason_for_cancellation = $request->reason_for_cancellation;
        $purchase->cancelled_at = Carbon::now();

        if(Auth::user()->hasRole(['ceo', 'cto', 'gabu.employee']))
        {
            $purchase->who_canceled = 'employee';
            $purchase->employee_id = Auth::user()->employee->id;
        }
        else if(Auth::user()->hasRole(['commerce.owner', 'commerce.employee']))
        {
            $purchase->who_canceled = 'merchant';
            $purchase->merchant_id = Auth::user()->merchant->id;
        }
        else
        {
                $purchase->who_canceled = 'client';
        }

        if($purchase->save())
        {
            $product = Product::find($purchase->product_id);
            $product->quantity_available = $product->quantity_available + $purchase->amount;
            $product->sales = $product->sales - $purchase->amount;
            if($product->quantity_available > 0)
                $product->status = 'active';

            $product->save();

            return response()->json(['status'    =>  'success'], 200);
        }

        return response()->json(['error' => 'No se pudo actualizar la Compra'], 422);
    }

    public function changeToCompleted(Request $request, $idPurchase)
    {
        /**
         * employee_id y merchant_id será quienes aceptaron o rechazaron la compra
         */

        $purchase = Purchase::find($idPurchase);

        if(!$purchase)
            return response()->json(['error'   =>  'La Compra no existe'], 422);

        $validator = Validator::make($request->all(),
        [
            'flag_completed'            => 'required|boolean'
        ],
        [
            'flag_completed.required'   =>  'El Flag de Completado es requerido',
            'flag_completed.boolean'    =>  'El Flag de Completado debe ser 1 o 0',
        ]);

        if($validator->fails())
            return response()->json(['errors' => $validator->errors()], 422);

        if(Auth::user()->hasRole(['commerce.owner', 'commerce.employee']))
        {
            if(Auth::user()->merchant->commerce->id != $purchase->commerce_id)
                return response()->json(['error'   =>  'La Venta no pertenece a su Comercio'], 422);
        }

        if(Auth::user()->hasRole(['client']))
        {
            if($purchase->client_id != Auth::user()->client->id)
                return response()->json(['error'   =>  'La Compra no le pertenece'], 422);
        }

        if($purchase->status == 'cancelled')
            return response()->json(['error'   =>  'Esta Compra ya fue Cancelada'], 422);

        if($purchase->status == 'completed')
            return response()->json(['error'   =>  'Esta Compra ya fue Completada'], 422);

        if(Auth::user()->hasRole(['ceo', 'cto', 'gabu.employee']))
        {
            $purchase->commerce_completed = $request->flag_completed;
            $purchase->employee_id = Auth::user()->employee->id;
        }
        else if(Auth::user()->hasRole(['commerce.owner', 'commerce.employee']))
        {
            $purchase->commerce_completed = $request->flag_completed;
            $purchase->merchant_id = Auth::user()->merchant->id;
        }
        else
        {
            $purchase->client_completed = $request->flag_completed;
        }

        if($purchase->client_completed == true && $purchase->commerce_completed == true)
            $purchase->status = 'completed';


        if($purchase->save())
            return response()->json(['status'    =>  'success'], 200);

        return response()->json(['error' => 'No se pudo actualizar la Compra'], 422);

        //Puede usarlo el cliente o comerciante, y solo afectaría su flag relacionado
        // Verificar si los dos flags son true, si es así cambiar el status del mismo
    }

    public function cleanPurchase($idPurchase)
    {
        // Permite a los empleados limpiar el pedido, es decir, dejarlo en un status inicial

        $purchase = Purchase::find($idPurchase);

        if(!$purchase)
            return response()->json(['error'   =>  'La Compra no existe'], 422);

        $purchase->status = 'active';
        $purchase->client_completed = false;
        $purchase->commerce_completed = false;
        $purchase->who_canceled = null;
        $purchase->reason_for_cancellation = null;
        $purchase->merchant_id = null;
        $purchase->employee_id = null;
        $purchase->cancelled_at = null;

        if($purchase->save())
            return response()->json(['status'    =>  'success'], 200);

        return response()->json(['error' => 'No se pudo limpiar la Compra'], 422);
    }
}
