<?php

use App\Models\Wompi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

function authenticationInWompi() : bool {

    $response;

    // Valido si se debe realizar la autenticación o no
    if(DB::table('wompis')->count() == 0) {
        //Primer uso y no tiene algún registro
        return authWithCurl(true);
    }

    $wompi = Wompi::find(1);

    $endDurationToken = Carbon::parse($wompi->calculated_expiration)->subSeconds(120);

    if(Carbon::now() >= $endDurationToken)
        return authWithCurl(false);

    return true;
}

// createWompi indica si es actualizar la fila o crear uno nuevo
function authWithCurl(bool $createWompi) : bool {
    // Aquí es donde se hace realmente la autenticación

    $postFields = "grant_type=" . env('grant_type', 'N/A GT') . "&";
    $postFields = $postFields . "client_id=" . env('client_id', 'N/A CI') . "&";
    $postFields = $postFields . "client_secret=" . env('client_secret', 'N/A CS') . "&";
    $postFields = $postFields . "audience=" . env('audience', 'N/A A');

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => "https://id.wompi.sv/connect/token",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => $postFields,
    CURLOPT_HTTPHEADER => array(
            "content-type: application/x-www-form-urlencoded"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);


    if ($err) {
        Log::error("Auth Wompi: cURL Error #:" . $err);
        return false;
    }

    $wompi;

    if($createWompi)
        $wompi = new Wompi();
    else
        $wompi = Wompi::find(1);

    $responseDecode = json_decode($response);

    $wompi->access_token = $responseDecode->{'access_token'};
    $wompi->expires_in = $responseDecode->{'expires_in'};
    $wompi->token_type = $responseDecode->{'token_type'};
    $wompi->scope = $responseDecode->{'scope'};
    $wompi->calculated_expiration = Carbon::now()->addSeconds($responseDecode->{'expires_in'});

    $wompi->save();

    return true;
}


