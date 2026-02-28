<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserRoles;
use App\Models\User;
use App\Models\Products\Product;
use App\Models\Products\ProductCategory;
use App\Models\Branch;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return match($user->role) {
            UserRoles::SUPER_ADMIN => inertia('dashboards/Admin', [
                'user' => $user,
                'stats' => $this->getAdminStats(),
            ]),
            UserRoles::ADMIN => inertia('dashboards/Admin', [
                'user' => $user,
                'stats' => $this->getAdminStats(),
            ]),
            UserRoles::CASHIER => inertia('dashboards/Manager', [
                'user' => $user,
                'team' => 'team stats',
            ]),
            default => inertia('dashboards/User', [
                'user' => $user,
                'activity' => 'activity',
            ]),
        };
    }

        private function getAdminStats()
    {
        return [
            'total_staff' => User::staff()->count(),
            'total_customers' => User::customers()->count(),
            'total_products' => Product::count(),
            'total_product_categories' => ProductCategory::count(),
            'total_branches' => Branch::count(),
            'total_sales' => 4578,
            'gross_sales' => 1000000,
            'net_sales' => 850000,
            'cost_of_sales' => 150000,
            'gross_profit' => 500000,
            // 'todaySales' => Transaction::today()->sum('amount'),
            // ... other admin specific stats
        ];
    }

    // private function getCashierStats()
    // {
    //     return [
    //         'currentShiftSales' => Shift::current()->sum('amount'),
    //         'transactionsToday' => Transaction::today()->count(),
    //         // ... other cashier specific stats
    //     ];
    // }

    // private function getCustomerOrders($user)
    // {
    //     return Order::where('user_id', $user->id)
    //         ->latest()
    //         ->take(10)
    //         ->get();
    // }
}
