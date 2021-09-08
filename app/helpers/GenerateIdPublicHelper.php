<?php

use App\Models\Client;
use App\Models\Employee;
use App\Models\Merchant;
use App\Models\Commerce;
use Illuminate\Support\Str;

function generateIdPublic() :   string
{

    $auxIdPublic;

    while (true)
    {
        $auxIdPublic = Str::random(24);

        $auxC = Client::where('id_public', $auxIdPublic)->count();
        $auxE = Employee::where('id_public', $auxIdPublic)->count();
        $auxM = Merchant::where('id_public', $auxIdPublic)->count();
        $auxC2 = Commerce::where('id_public', $auxIdPublic)->count();

        if($auxC == 0 && $auxE == 0 && $auxM == 0 && $auxC2 == 0)
            break;
    }

    return $auxIdPublic;


}
