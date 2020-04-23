<?php


namespace App\Controllers;


use App\Http\Request;
use App\Models\Users;
use const App\Database\config;

/**
 * Class Controller
 * @package App\Controllers
 */
class Controller
{
    /**
     * @param Request $request
     */
    function index(Request $request)
    {



        $users=Users::get();
        return view('index', compact('users'));


    }
}