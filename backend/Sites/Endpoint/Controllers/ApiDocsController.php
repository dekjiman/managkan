<?php

namespace Sites\Endpoint\Controllers;

use CodeIgniter\Controller;

class ApiDocsController extends Controller
{
    public function index()
    {
        return view('api-docs/index');
    }
}
