<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Branch;
use App\Http\Requests\Branches\BranchRequest;

class BranchesController extends Controller
{
    public function index(Request $request)
    {
        $query = Branch::query();

        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $branches = $query->latest()->get();

        return Inertia::render('branches/Index', [
            'branches' => $branches,
            'total' => $branches->count(),
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        return Inertia::render('branches/Create');
    }

    public function store(BranchRequest $request)
    {
        Branch::create([
            'name' => $request->name,
            'code' => $request->code,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'city' => $request->city,
        ]);

        return redirect()->route('branches.index')->with(['message' => 'Branch created successfully', 'type' => 'success']);
    }

    public function edit(Branch $branch)
    {
        return Inertia::render('branches/Edit', [
            'branch' => $branch,
        ]);
    }

    public function update(BranchRequest $request, Branch $branch)
    {
        $updated_data = [
            'name' => $request->name,
            'code' => $request->code,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'city' => $request->city,
        ];

        $branch->update($updated_data);

        return redirect()->route('branches.index')->with(['message' => 'Branch updated successfully', 'type' => 'success']);
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();

        return redirect()->route('branches.index')->with(['message' => 'Branch deleted successfully', 'type' => 'success']);
    }
}
