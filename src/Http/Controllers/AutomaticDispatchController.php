<?php

namespace Codificar\MarketplaceIntegration\Http\Controllers;

use App\Http\Controllers\Controller;

use Codificar\MarketplaceIntegration\Http\Requests\AutomaticDispatchStoreFormRequest;
use Codificar\MarketplaceIntegration\Http\Requests\AutomaticDispatchFormRequest;
use Codificar\MarketplaceIntegration\Http\Resources\AutomaticDispatchResource;

use Codificar\MarketplaceIntegration\Repositories\DispatchRepository;

use Codificar\MarketplaceIntegration\Models\AutomaticDispatch;

class AutomaticDispatchController extends Controller
{

    public function get(AutomaticDispatchFormRequest $request) {

        $resource = [
            'success'               => true,
            'institution_id'        => $request->institution_id,
            'provider_type_id'      => $request->automaticDispatch->provider_type_id,
            'wait_time_limit'       => $request->automaticDispatch->wait_time_limit,
            'max_delivery'          => $request->automaticDispatch->max_delivery
        ];

        return new AutomaticDispatchResource($resource);
    }

    public function store(AutomaticDispatchStoreFormRequest $request) {

        $automaticDispatch = DispatchRepository::getAutomaticDispatch($request->institution_id);

        if(!$automaticDispatch)
            $automaticDispatch = new AutomaticDispatch();

        $automaticDispatch->institution_id      = $request->institution_id;
        $automaticDispatch->provider_type_id    = $request->provider_type_id;
        $automaticDispatch->wait_time_limit     = $request->wait_time_limit;
        $automaticDispatch->max_delivery        = $request->max_delivery;
        $automaticDispatch->save();

        $resource = [
            'success'               => true,
            'institution_id'        => $automaticDispatch->institution_id,
            'provider_type_id'      => $automaticDispatch->provider_type_id,
            'wait_time_limit'       => $automaticDispatch->wait_time_limit,
            'max_delivery'          => $automaticDispatch->max_delivery
        ];

        return new AutomaticDispatchResource($resource);
    }

    public function delete(AutomaticDispatchFormRequest $request) {

        $request->automaticDispatch->delete();

        $resource = [
            'success'               => true,
            'institution_id'        => $request->institution_id
        ];

        return new AutomaticDispatchResource($resource);
    }
}
