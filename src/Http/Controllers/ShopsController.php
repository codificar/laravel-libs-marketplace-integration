<?php

namespace Codificar\MarketplaceIntegration\Http\Controllers;

use Codificar\MarketplaceIntegration\Http\Requests\ShopsFormRequest;
use Codificar\MarketplaceIntegration\Http\Resources\ShopResource;
use Codificar\MarketplaceIntegration\Models\Shops;
use App\Http\Controllers\Controller;
use App\Repositories\SettingsRepository;
use Codificar\MarketplaceIntegration\Http\Repositories\ShopsRepository;
use Codificar\MarketplaceIntegration\Http\Requests\IFoodCredentialsFormRequest;
use Codificar\MarketplaceIntegration\Http\Requests\UpdateStatusReloadFormRequest;
use Codificar\MarketplaceIntegration\Http\Resources\SettingsResource;

class ShopsController extends Controller
{
    /**
     * Get all shops in database
     * @author Diogo C. Coutinho
     * 
     * @return ShopResource $data
     */
    public function index()
    {
        $institutionId = \Auth::guard('web_corp')->user()->AdminInstitution->institution_id;
        $shops = ShopsRepository::getAllShops($institutionId);
        return new ShopResource($shops);
    }

    /**
     * Store shop in database
     * @author Diogo C. Coutinho
     * 
     * @param String $name
     * 
     * @return ShopResource $shop
     */
    public function store(ShopsFormRequest $request)
    {        
        $institutionId = \Auth::guard('web_corp')->user()->AdminInstitution->institution_id;
        $shop = ShopsRepository::updateOrCreateShop($request, $institutionId);
        return new ShopResource($shop);
    }

    /**
     * Update status reload in shop database
     * @author Diogo C. Coutinho
     * 
     * @param UpdateStatusRelaodFormRequest $request
     * 
     * @return Shop $data
     */
    public function updateStatusReload(UpdateStatusReloadFormRequest $request)
    {
        $institutionId = \Auth::guard('web_corp')->user()->AdminInstitution->institution_id;
        return ShopsRepository::updateStatusReload($request->status_reload, $institutionId);
        
    }

    /**
     * Update shop in database
     * @author Diogo C. Coutinho
     * 
     * @param ShopsFormRequest $request
     * 
     * @return Shops $data
     */
    public function updateShop(ShopsFormRequest $request)
    {
        $institutionId = \Auth::guard('web_corp')->user()->AdminInstitution->institution_id;
        $shop = ShopsRepository::updateOrCreateShop($request, $institutionId);
        return new ShopResource($shop);
    }

    /**
     * Delete shop from database
     * @author Diogo C. Coutinho
     * 
     * @param Integer $id
     * 
     * @return Boolean true|false
     */
    public function deleteShop($id)
    {
        $data = Shops::find($id);
        if (is_object($data))
        {
            $data->delete();
            return true;
        } 
        else 
        {
            return false;
        }
    }

    /**
     * Update or create iFood credentials in database
     * @author Diogo C. Coutinho
     * 
     * @param IFoodCredentialsFormRequest $request
     * 
     * @return Array $response
     */
    public function updateOrCreateIFoodCredentials(IFoodCredentialsFormRequest $request)
    {   
        $data = SettingsRepository::updateOrCreateCredentialsIFood($request->all());

        if ($data['client_id'] && $data['client_secret']) {
            return new SettingsResource([
                'code'      => 200,
                'message'   => 'Salvo com sucesso!'
            ]);
        } else {
            return new SettingsResource([
                'code'      => 401,
                'message'   => 'Erro ao salvar as credenciais!'
            ]);
        }
    }

    /**
     * Get iFood Credentials in database
     * @author Diogo C. Coutinho
     * 
     * @return SettingsResource $data
     */
    public function getIfoodCredentials()
    {
        $client_id          = \Settings::where('key', 'ifood_client_id')->first();
        $client_secret      = \Settings::where('key', 'ifood_client_secret')->first();
        if ($client_secret && $client_id) {
            return new SettingsResource([
                'ifood_client_id'       => $client_id,
                'ifood_client_secret'   => $client_secret
            ]);
        } else {
            return new SettingsResource([
                'code'      => 404,
                'message'   => 'Cadastre as cerdenciais iFood!'
            ]);
        }
    }
}
