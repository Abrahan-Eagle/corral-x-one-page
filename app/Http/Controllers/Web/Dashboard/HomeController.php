<?php

namespace App\Http\Controllers\Web\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Profile;
use App\Models\Product;
use App\Models\Ranch;
use App\Models\Order;
use App\Models\Conversation;
use App\Models\Role;
use App\Models\Review;
use App\Models\Favorite;
use App\Models\Report;
use App\Models\Advertisement;

class HomeController extends Controller
{
    /**
     * Display the dashboard with statistics.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $stats = [
            'users' => User::count(),
            'profiles' => Profile::count(),
            'products' => Product::count(),
            'products_active' => Product::where('status', 'active')->count(),
            'products_sold' => Product::where('status', 'sold')->count(),
            'ranches' => Ranch::count(),
            'orders' => Order::count(),
            'orders_pending' => Order::where('status', 'pending')->count(),
            'orders_completed' => Order::where('status', 'completed')->count(),
            'conversations' => Conversation::where('is_active', true)->count(),
            'reviews' => Review::count(),
            'favorites' => Favorite::count(),
            'reports' => Report::where('status', 'pending')->count(),
            'advertisements' => Advertisement::where('is_active', true)->count(),
            'roles' => Role::count(),
        ];

        // Usuarios recientes (últimos 5)
        $recent_users = User::with('profile')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Productos recientes (últimos 5)
        $recent_products = Product::with(['ranch.profile'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.home', compact('stats', 'recent_users', 'recent_products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Toggle light/dark theme for the authenticated user.
     * Implementación basada en uniblockx
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $id = auth()->user()->id;
        $user = User::find($id);

        // Comparar con strings como en uniblockx
        switch ($user->light) {
            case '1':
                $light = '0';
                break;

            case '0':
                $light = '1';
                break;
            
            default:
                $light = '1';
                break;
        }

        // Usar update directo en query builder como en uniblockx
        User::where('id', '=', $id)
            ->update(['light' => $light]);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
