<?php

namespace Codificar\MarketplaceIntegration\Http\Controllers;
use App\Http\Controllers\Controller;

class SinglePageController extends Controller
{
    public function index() {
        return view('app');
    }
}
