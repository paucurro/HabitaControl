<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use App\Http\Responses\PrettyJsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class CartController extends BaseController
{
    /**
     * @param string $peticion
     * @return string
     */
    public function getCeroSuccesfull($peticion)
    {
        return Artisan::call($peticion) == 0 ? 'Success' : 'Error';
    }


    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function execute_cart(Request $request) 
    {

        $response = [
            'PHP' => phpversion(),
            'ArtisanVersion' => App::version(),
            'Environment' => config('app.env'),
            'AppVersion' => config('app.app_version', '1.0B'),
            'Clear' => $this->getCeroSuccesfull('clear-compiled'),
            'configClear' => $this->getCeroSuccesfull('config:clear'),
            'configCache' => $this->getCeroSuccesfull('config:cache'),
            'clearCache' => $this->getCeroSuccesfull('cache:clear'),
            'routeClear' => $this->getCeroSuccesfull('route:clear'),
            'viewClear' => $this->getCeroSuccesfull('view:clear'),
            // 'optimize' => $this->getCeroSuccesfull('optimize'),
            'momento' => date("Y-m-d H:i:s")
        ];

        return new PrettyJsonResponse($response);
    }

}
