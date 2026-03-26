<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserRoles;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === UserRoles::SUPER_ADMIN) {
            return inertia('dashboards/SuperAdmin', [
                'user' => $user,
                'stats' => $this->getSuperAdminStats(),
            ]);
        }
    }

    private function getSuperAdminStats()
    {
        return [
            // 'total_staff' => User::staff()->count(),
            // 'total_customers' => User::customers()->count(),
            'total_staff' => 1000,
            'toal_customers' => 1000,

            // 'total_products' => Product::count(),
            // 'total_product_categories' => ProductCategory::count(),
            // 'total_branches' => Branch::count(),
            'total_products' => 1000,
            'total_product_categories' => 1000,
            'total_product_branches' => 1000,

            'total_sales' => 4578,
            'gross_sales' => 1000000,
            'net_sales' => 850000,
            'cost_of_sales' => 150000,
            'gross_profit' => 500000,

            // 'todaySales' => Transaction::today()->sum('amount'),
        ];
    }
}
