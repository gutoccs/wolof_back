<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Purchase;
use App\Models\Wompi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(request $request)
    {
        // Consulta condicionada a una compra

        if(!$request->exists('purchase_id'))
            return response()->json(['error'   =>  'Debe indicar el ID de la Compra'], 422);

        $payments = Payment::leftJoin('purchases', 'purchases.id', '=', 'payments.purchase_id')
                            ->where('payments.purchase_id', $request->purchase_id);

        if(Auth::user()->hasRole(['commerce.owner', 'commerce.employee']))
        {
            $payments = $payments->where('purchases.commerce_id', Auth::user()->merchant->commerce->id);
        }

        if(Auth::user()->hasRole('client'))
        {
            $payments = $payments->where('purchases.client_id', Auth::user()->client->id);
        }

        $payments = $payments->select('payments.id as id', 'payments.purchase_id as purchase_id', 'payments.flag_error as flag_error', 'payments.id_transaccion as id_transaccion', 'payments.es_real as es_real', 'payments.es_aprobada as es_aprobada', 'payments.codigo_autorizacion as codigo_autorizacion', 'payments.mensaje as mensaje', 'payments.forma_pago as forma_pago', 'payments.monto as monto', 'payments.servicio_error as servicio_error', 'payments.mensajes_error as mensajes_error', 'payments.created_at as created_at', 'payments.updated_at as updated_at')
                                ->get();


        return response()->json(
            [
                'status'    =>  'success',
                'payments'  =>  $payments
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
        // Solo usado por clientes

        $validator = Validator::make($request->all(),
        [
            'purchase_id'           =>  'required|numeric|exists:purchases,id',
            'numero_tarjeta'        =>  'required|string|between:12,20',
            'cvv'                   =>  'required|string|between:3,4',
            'mes_vencimiento'       =>  'required|numeric|between:1:12',
            'anio_vencimiento'      =>  'required|numeric|between:2018,2030',

        ],
        [
            'purchase_id.required'      =>  'El ID de la Compra es requerido',
            'purchase_id.numeric'       =>  'El ID de la Compra debe ser numérico',
            'purchase_id.exists'        =>  'El ID de la Compra no existe',
            'numero_tarjeta.required'   =>  'El Número de la Tarjeta es requerido',
            'numero_tarjeta.string'     =>  'El Número de la Tarjeta debe ser un String (texto)',
            'numero_tarjeta.between'    =>  'El Número de la Tarjeta debe tener entre 12 y 20 dígitos',
            'cvv.required'              =>  'El Número CVV de la Tarjeta es requerido',
            'cvv.string'                =>  'El Número CVV de la Tarjeta debe ser un String (texto)',
            'cvv.between'               =>  'El Número CVV de la Tarjeta debe tener 3 o 4 dígitos',
            'mes_vencimiento.required'  =>  'El Mes de Vencimiento de la Tarjeta (numérico) es requerido',
            'mes_vencimiento.numeric'   =>  'El Mes de Vencimiento de la Tarjeta debe ser un número',
            'mes_vencimiento.between'   =>  'El Mes de Vencimiento de la Tarjeta debe ser entre 1 y 12',
            'anio_vencimiento.required' =>  'El Año de Vencimiento de la Tarjeta es requerido',
            'anio_vencimiento.numeric'  =>  'El Año de Vencimiento de la Tarjeta debe ser un número',
            'anio_vencimiento.between'  =>  'El Año de Vencimiento de la Tarjeta debe ser entre 2018 y 2030',
        ]);

        if($validator->fails())
            return response()->json(['errors'   =>  $validator->errors()], 422);

        if(!authenticationInWompi())
            return response()->json(['error'   =>  'En estos momentos no se puede efectuar el Pago'], 422);


        $purchase = Purchase::find($request->purchase_id);

        if($purchase->flag_paid_out)
            return response()->json(['error'   =>  'La compra ya está paga'], 422);

        $wompi = Wompi::find(1);

        $data = array(
            'tarjetaCreditoDebido' => array(
                'numeroTarjeta'     => $request->numero_tarjeta,
                'cvv'               => $request->cvv,
                'mesVencimiento'    => $request->mes_vencimiento,
                'anioVencimiento'   => $request->anio_vencimiento,
            ),
            'monto'         => $purchase->total_to_pay,
            'emailCliente'  => $purchase->client->user->email,
            'nombreCliente' => $purchase->client->name . ' ' . $purchase->client->surname,
        );



        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.wompi.sv/TransaccionCompra",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => array(
                "content-type: application/json",
                "Authorization: Bearer " . $wompi->access_token
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        $payment = new Payment();

        $payment->purchase_id = $purchase->id;

        if ($err) {
            $this->errorResponse($payment, $err);
            $responseDecode = json_decode($err);
            return response()->json(['errors' => $responseDecode->{'mensajes'}], 422);
        }

        $responseDecode = json_decode($response);

        if(isset($responseDecode->{'servicioError'})) {
            $this->errorResponse($payment, $response);
            return response()->json(['errors' => $responseDecode->{'mensajes'}], 422);
        }

        $payment->flag_error = false;

        $payment->id_transaccion = $responseDecode->{'idTransaccion'};
        $payment->es_real = $responseDecode->{'esReal'};
        $payment->es_aprobada = $responseDecode->{'esAprobada'};
        if($payment->es_aprobada)
            $payment->codigo_autorizacion = $responseDecode->{'codigoAutorizacion'};
        $payment->forma_pago = $responseDecode->{'formaPago'};
        $payment->monto = $responseDecode->{'monto'};

        if($payment->es_aprobada && $payment->es_real){
            $purchase->flag_paid_out = true;
            $purchase->save();
        }

        $payment->save();

        return response()->json(['status' => 'success'], 200);
    }

    private function errorResponse(Payment $payment, $err) {

        Log::error("Payment Wompi: cURL Error #:" . $err);

        $responseDecode = json_decode($err);

        $payment->flag_error = true;

        $payment->servicio_error = $responseDecode->{'servicioError'};

        $msgError = '';
        foreach ($responseDecode->{'mensajes'} as $valor) {
            $msgError = $msgError . $valor . ',';
        }

        $payment->mensajes_error = mb_substr($msgError, 0, -1);

        $payment->save();
    }



    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function show($idPayment)
    {

        if(Payment::where('id', $idPayment)->count() == 0)
            return response()->json(['error'    =>  'El ID del Pago no existe'], 422);

        $payment = Payment::leftJoin('purchases', 'purchases.id', '=', 'payments.purchase_id')
                                ->where('payments.id', $idPayment);

        if(Auth::user()->hasRole(['commerce.owner', 'commerce.employee']))
        {
            $payment = $payment->where('purchases.commerce_id', Auth::user()->merchant->commerce->id);
        }

        if(Auth::user()->hasRole('client'))
        {
            $payment = $payment->where('purchases.client_id', Auth::user()->client->id);
        }

        $payment = $payment->select('payments.id as id', 'payments.purchase_id as purchase_id', 'payments.flag_error as flag_error', 'payments.id_transaccion as id_transaccion', 'payments.es_real as es_real', 'payments.es_aprobada as es_aprobada', 'payments.codigo_autorizacion as codigo_autorizacion', 'payments.mensaje as mensaje', 'payments.forma_pago as forma_pago', 'payments.monto as monto', 'payments.servicio_error as servicio_error', 'payments.mensajes_error as mensajes_error', 'payments.created_at as created_at', 'payments.updated_at as updated_at')
                                ->first();

        return response()->json(
        [
            'status'    =>  'success',
            'payment'  =>  $payment
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Payment $payment)
    {
        // No hay actualización porque esto se genera automáticamente cuando el cliente paga
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Payment $payment)
    {
        // No hay borrado porque esto se genera automáticamente cuando el cliente paga
    }

    public function approvedPurchasePayment(Request $request)
    {
        //Envía el pago aprobado dado una compra

        if(!$request->exists('purchase_id'))
            return response()->json(['error'   =>  'Debe indicar el ID de la Compra'], 422);

        $payment = Payment::leftJoin('purchases', 'purchases.id', '=', 'payments.purchase_id')
                            ->where('payments.purchase_id', $request->purchase_id)
                            ->where('payments.es_aprobada', true);

        if(Auth::user()->hasRole(['commerce.owner', 'commerce.employee']))
        {
            $payment = $payment->where('purchases.commerce_id', Auth::user()->merchant->commerce->id);
        }

        if(Auth::user()->hasRole('client'))
        {
            $payment = $payment->where('purchases.client_id', Auth::user()->client->id);
        }

        $payment = $payment->select('payments.id as id', 'payments.purchase_id as purchase_id', 'payments.flag_error as flag_error', 'payments.id_transaccion as id_transaccion', 'payments.es_real as es_real', 'payments.es_aprobada as es_aprobada', 'payments.codigo_autorizacion as codigo_autorizacion', 'payments.mensaje as mensaje', 'payments.forma_pago as forma_pago', 'payments.monto as monto', 'payments.servicio_error as servicio_error', 'payments.mensajes_error as mensajes_error', 'payments.created_at as created_at', 'payments.updated_at as updated_at')
                                ->first();


        return response()->json(
            [
                'status'    =>  'success',
                'payment'  =>  $payment
            ], 200);
    }
}
