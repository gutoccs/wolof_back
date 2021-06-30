<?php

use App\models\Client;
use App\models\Employee;
use App\models\Merchant;
use App\models\Shop;

function generateIdPublic() :   string
{

    $auxIdPublic;

    while (true)
    {
        $auxIdPublic = Str::random(24);

        $auxC = Client::where('id_public', $auxIdPublic)->count();
        $auxE = Employee::where('id_public', $auxIdPublic)->count();
        $auxM = Merchant::where('id_public', $auxIdPublic)->count();
        $auxS = Shop::where('id_public', $auxIdPublic)->count();

        if($auxC == 0 && $auxE == 0 && $auxM == 0 && $auxS == 0)
            break;
    }

    return $auxIdPublic;


}
