<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Branch;
use App\Http\Requests\Users\UserRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Enums\UserRoles;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $role_counts = User::selectRaw('role, COUNT(*) as count')
            ->groupBy('role')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->role->value => [
                        'count' => $item->count,
                        'label' => $item->role->label()
                    ]
                ];
            });

        $users = User::query()
            ->when($request->search, fn($q, $search) => $q->search($search))
            ->when($request->has('role') && $request->role !== '', fn($q, $role) => $q->filterByRole($request->role))
            ->orderByRolePriority()
            ->paginate(20);

        return Inertia::render('users/Index', [
            'users' => $users,
            'role_counts' => $role_counts,
            'filters' => $request->only(['search', 'role'])
        ]);
    }

    public function create()
    {
        $branches = Branch::select('id', 'name')->get();

        /** @var \App\Models\User $current_user */
        $current_user = Auth::user();

        $role_options = [];

         foreach (UserRoles::cases() as $role) {
            // Super admin can create anyone
            if ($current_user->isSuperAdmin()) {
                $role_options[] = [
                    'value' => $role->value,
                    'label' => $role->label(),
                ];
                continue;
            }
            
            // Admin can create: ADMIN, CASHIER, CUSTOMER, SUPPLIER
            if ($current_user->isAdmin()) {
                if ($role === UserRoles::SUPER_ADMIN) {
                    continue; // Admin can't create super admin
                }
                $role_options[] = [
                    'value' => $role->value,
                    'label' => $role->label(),
                ];
                continue;
            }
            
            // Cashier can only create: CUSTOMER
            if ($current_user->isCashier() && $role === UserRoles::CUSTOMER) {
                $role_options[] = [
                    'value' => $role->value,
                    'label' => $role->label(),
                ];
            }
            // Default: no permissions (shouldn't reach here if auth middleware works)
        }

        return Inertia::render('users/Create', compact('branches', 'role_options'));
    }

    public function store(UserRequest $request)
    {
        DB::transaction(function() use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'status' => 1,
            ]);

            // Create appropriate profile based on role
            $role = UserRoles::from($request->role);

            switch ($role) {
                case UserRoles::CASHIER:
                    $user->staffProfile()->create([
                        'staff_code' => 'STF-' . now()->timestamp,
                        'position' => $request->position,
                        'branch_id' => $request->branch_id,
                        'hired_at' => now(),
                    ]);
                    break;
                    
                case UserRoles::CUSTOMER:
                    $user->customerProfile()->create([
                        'customer_code' => 'CUST-' . now()->timestamp,
                        'credit_limit' => $request->credit_limit ?? 0,
                        'loyalty_points' => $request->loyalty_points ?? 0,
                    ]);
                    break;
                    
                case UserRoles::SUPPLIER:
                    $user->supplierProfile()->create([
                        'company_name' => $request->company_name,
                        'payment_terms' => $request->payment_terms,
                    ]);
                    break;
            }
        });
        
        return redirect()->route('users.index')->with(['message' => 'User created successfully', 'type' => 'success']);
    }

    public function edit(User $user)
    {
        $branches = Branch::select('id', 'name')->get();

        /** @var \App\Models\User $current_user */
        $current_user = Auth::user();

        $role_options = [];

        foreach(UserRoles::cases() as $role) {
            // Super admin can edit anyone (but can't change super admin to lower role)
            if ($current_user->isSuperAdmin()) {
                // If editing a super admin, only allow super admin role
                if ($user->isSuperAdmin() && $role !== UserRoles::SUPER_ADMIN) {
                    continue;
                }
                $role_options[] = [
                    'value' => $role->value,
                    'label' => $role->label(),
                ];
                continue;
            }

            // Admin can edit: ADMIN, CASHIER, CUSTOMER, SUPPLIER
            if ($current_user->isAdmin()) {
                if ($role === UserRoles::SUPER_ADMIN) {
                    continue; // Admin can't edit super admin
                }
                $role_options[] = [
                    'value' => $role->value,
                    'label' => $role->label(),
                ];
                continue;
            }

            // Cashier can only edit: CUSTOMER (and only if user being updated is a customer)
            if ($current_user->isCashier() && $role === UserRoles::CUSTOMER && $user->isCustomer()) {
                $role_options[] = [
                    'value' => $role->value,
                    'label' => $role->label(),
                ];
            }
        }

        // Load user with their profile based on current role
        $user->load([
            'staffProfile',
            'customerProfile',
            'supplierProfile',
        ]);

        return Inertia::render('users/Edit', compact('user', 'branches', 'role_options'));
    }

    public function update(UserRequest $request, User $user)
    {
        DB::transaction(function() use ($request, $user) {
            $update_data = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            // Only update role if it's changed (and user has permission)
            if ($request->has('role') && $request->role != $user->role->value) {
                $update_data['role'] = $request->role;
            }
            
            // Only update password if provided
            if ($request->filled('password')) {
                $update_data['password'] = Hash::make($request->password);
            }
            
            $user->update($update_data);
            
            // Handle role change or profile update
            $new_role = $request->has('role') ? UserRoles::from($request->role) : $user->role;
            
            // If role changed, delete old profile and create new one
            if ($request->has('role') && $request->role != $user->getOriginal('role')) {
                // Delete old profile
                $user->staffProfile()->delete();
                $user->customerProfile()->delete();
                $user->supplierProfile()->delete();
                
                // Create new profile based on new role
                switch ($new_role) {
                    case UserRoles::CASHIER:
                        $user->staffProfile()->create([
                            'staff_code' => 'STF-' . now()->timestamp,
                            'position' => $request->position,
                            'branch_id' => $request->branch_id,
                            'hired_at' => now(),
                        ]);
                        break;
                        
                    case UserRoles::CUSTOMER:
                        $user->customerProfile()->create([
                            'customer_code' => 'CUST-' . now()->timestamp,
                            'credit_limit' => $request->credit_limit ?? 0,
                            'loyalty_points' => $request->loyalty_points ?? 0,
                        ]);
                        break;
                        
                    case UserRoles::SUPPLIER:
                        $user->supplierProfile()->create([
                            'company_name' => $request->company_name,
                            'payment_terms' => $request->payment_terms,
                        ]);
                        break;
                }
            } else {
                // Update existing profile based on current role
                switch ($user->role) {
                    case UserRoles::CASHIER:
                        if ($user->staffProfile) {
                            $user->staffProfile()->update([
                                'position' => $request->position,
                                'branch_id' => $request->branch_id,
                            ]);
                        }
                        break;
                        
                    case UserRoles::CUSTOMER:
                        if ($user->customerProfile) {
                            $user->customerProfile()->update([
                                'credit_limit' => $request->credit_limit ?? 0,
                                'loyalty_points' => $request->loyalty_points ?? 0,
                            ]);
                        }
                        break;
                        
                    case UserRoles::SUPPLIER:
                        if ($user->supplierProfile) {
                            $user->supplierProfile()->update([
                                'company_name' => $request->company_name,
                                'payment_terms' => $request->payment_terms,
                            ]);
                        }
                        break;
                }
            }
        });
        
        return redirect()->route('users.index')->with(['message' => 'User updated successfully', 'type' => 'success']);
    }

    public function destroy(User $user)
    {
        if (Gate::denies('delete', $user)) {
            return redirect()->route('users.index')->with([
                'message' => 'You do not have permission to delete this user',
                'type' => 'error'
            ]);
        }

        $user->delete();
        
        return redirect()->route('users.index')->with([
            'message' => 'User deleted successfully',
            'type' => 'success'
        ]);
    }
}
