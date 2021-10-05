<?php 

namespace Codificar\MarketplaceIntegration\Http\Repositories;

use App\Models\Settings;

class SettingsRepository
{
    /**
     * Update or create iFood credentials in database
     * @author Diogo C. Coutinho
     * 
     * @param Object $request
     * 
     * @return Array $response
     */
    public static function updateOrCreateCredentialsIFood($credentialsData)
    {
        $clientId          = \Settings::updateOrCreate([
            'key'   =>  'ifood_client_id'],[
            'value' => $credentialsData->ifood_client_id
        ]);
        $clientSecret      = \Settings::updateOrCreate([
            'key'   =>  'ifood_client_secret'],[
            'value' => $credentialsData->ifood_client_secret
        ]);
        return [
            'clientId'      => $clientId,
            'clientSecret'  => $clientSecret
        ];
    }

    #TODO make a get keys and token functions
}