<?php

namespace App\Http\Controllers\Users;

use App\Models\User;
use App\Models\Branch;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Enums\UserRoles;
use App\Http\Resources\UserResource;
use App\Http\Requests\Users\UserRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Exception;


class UserController extends Controller
{
    public function index(Request $request)
    {
        $role_counts = $this->getRoleCounts();

        $users = User::query()
            ->with(['staffProfile', 'customerProfile', 'supplierProfile', 'creator', 'updater'])
            ->when($request->has('search') && $request->search, fn($q, $search) => $q->search($search))
            ->when($request->has('role') && $request->role !== '', fn($q) => $q->filterByRole($request->role))
            ->orderByRolePriority()
            ->paginate(20);

        return Inertia::render('users/Index', [
            'users' => UserResource::collection($users),
            'role_counts' => $role_counts,
            'filters' => $request->only(['search', 'role'])
        ]);
    }

    private function getRoleCounts(): array
    {
        $counts = DB::table('users')
            ->select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->get();

        $result = [];

        foreach ($counts as $item) {
            $role = UserRoles::tryFrom((int) $item->role);
            if ($role) {
                $result[$role->value] = [
                    'count' => $item->count,
                    'label' => $role->label(),
                    'value' => $role->value,
                ];
            }
        }

        // Sort by label but preserve keys
        uasort($result, function ($a, $b) {
            return $a['label'] <=> $b['label'];
        });

        return $result;
    }

    public function create()
    {
        $branches = Branch::select('id', 'name')->get();

        return Inertia::render('users/Create', [
            'branches' => $branches,
        ]);
    }

    public function store(UserRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'status' => 1,
            ]);

            $role = UserRoles::from($request->role);

            switch ($role) {
                case UserRoles::CASHIER:
                    $user->staffProfile()->create([
                        'position' => $request->position,
                        'hired_at' => $request->hired_at,
                        'branch_id' => $request->branch_id,
                    ]);
                    break;

                case UserRoles::SUPPLIER:
                    $user->supplierProfile()->create([
                        'company_name' => $request->company_name,
                        'payment_terms' => $request->payment_terms,
                        'tax_id' => $request->tax_id,
                        'is_active' => $request->is_active ?? true,
                        'branch_id' => $request->branch_id,
                    ]);
                    break;
                
                case UserRoles::CUSTOMER:
                    $user->customerProfile()->create([
                        'loyalty_points' => $request->loyalty_points ?? 0,
                        'credit_limit' => $request->credit_limit,
                        'branch_id' => $request->branch_id,
                    ]);
                    break;

                case UserRoles::SUPER_ADMIN:
                case UserRoles::ADMIN:
                    // Admin roles don't need additional profiles
                    break;
            }

            DB::commit();

            return redirect()
                ->route('users.index')
                ->with([
                    'message' => 'User created successfully',
                    'type' => 'success'
                ]);
        } catch (Exception $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->with([
                    'message' => 'Failed to create user: ' . $e->getMessage(),
                    'type' => 'error'
                ]);
        }
    }
}