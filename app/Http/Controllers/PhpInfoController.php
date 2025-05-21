<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PhpInfoController extends Controller
{
    public function index()
    {
        phpinfo();
        exit;
    }
}
