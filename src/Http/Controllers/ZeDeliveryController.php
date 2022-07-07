<?php

namespace Codificar\MarketplaceIntegration\Http\Controllers;

use App\Http\Controllers\Controller;

use Codificar\MarketplaceIntegration\Http\Requests\ZeDeliveryImportFinancial;

use Maatwebsite\Excel\Facades\Excel;

use Codificar\MarketplaceIntegration\Imports\ZeDeliveryImport;

class ZeDeliveryController extends Controller
{


    /**
     * Function to import the financial data ze delivery spreadsheet
     */
    public function import(ZeDeliveryImportFinancial $request)
    {
        Excel::queueImport(new ZeDeliveryImport, $request->file('csv_file'));

        return response()->json([
            'success' => true
        ]);
    }
}
