<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class AccueilController extends Controller
{
    /**
     * Summary of index
     * @return View
     */
    public function index() {
        return view('index');
    }
}
