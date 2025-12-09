<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    /**
     * Update user light/dark theme preference
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update()
    {
        $id = auth()->user()->id;
        $user = User::find($id);

        switch ($user->light) {
            case '1':
                $light = '0';
                break;
            case '0':
                $light = '1';
                break;
            default:
                $light = '1';
        }

        User::where('id', '=', $id)
            ->update(['light' => $light]);

        return back();
    }
}
