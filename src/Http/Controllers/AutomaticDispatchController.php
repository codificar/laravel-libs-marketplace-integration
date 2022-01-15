<?php

namespace Codificar\MarketplaceIntegration\Http\Controllers;

use App\Http\Controllers\Controller;

use Codificar\MarketplaceIntegration\Http\Requests\AutomaticDispatchFormRequest;
use Codificar\MarketplaceIntegration\Http\Resources\AutomaticDispatchResource;

class AutomaticDispatchController extends Controller
{

    public function get(AutomaticDispatchFormRequest $request) {

        $resource = [
            'institution_id'        => $request->institution_id,
            'provider_type_id'      => $request->automaticDispatch->provider_type_id,
            'wait_time_limit'       => $request->automaticDispatch->wait_time_limit,
            'max_delivery'          => $request->automaticDispatch->max_delivery
        ];

        return new AutomaticDispatchResource($resource);
    }
}
