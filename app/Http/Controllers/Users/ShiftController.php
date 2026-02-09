<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Users\Shift;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ShiftController extends Controller
{
    public function open()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // If user already has an open shift, redirect to POS
        if ($user->hasOpenShift()) {
            return redirect()->route('pos.index');
        }
        
        return Inertia::render('shifts/Open', [
            'lastShift' => $user->shifts()
                ->latest()
                ->whereNotNull('closed_at')
                ->first(),
        ]);
    }

    /**
     * Store a newly opened shift.
     */
    public function store(Request $request)
    {
        $request->validate([
            'opening_cash' => 'required|numeric|min:0|max:100000',
        ]);
        
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        // Check if user already has an open shift
        if ($user->hasOpenShift()) {
            return redirect()->route('pos.index');
        }
        
        $shift = Shift::create([
            'user_id' => $user->id,
            'opened_at' => now(),
            'opening_cash' => $request->opening_cash,
        ]);
        
        return redirect()->route('pos.index')
            ->with('success', 'Shift opened successfully.');
    }

    /**
     * Show the close shift form.
     */
    public function close()
    {
        $user = Auth::user();
        $shift = $user->openShift;
        
        if (!$shift) {
            return redirect()->route('shifts.open');
        }
        
        // Calculate expected cash (opening cash + cash sales - cash withdrawals)
        // You'll need to adjust this based on your payment structure
        $expectedCash = $shift->opening_cash;
        
        return Inertia::render('shifts/Close', [
            'shift' => $shift,
            'expectedCash' => $expectedCash,
        ]);
    }

    /**
     * Close the current shift.
     */
    public function update(Request $request)
    {
        $request->validate([
            'closing_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);
        
        $user = Auth::user();
        $shift = $user->openShift;
        
        if (!$shift) {
            return redirect()->route('shifts.open');
        }
        
        $shift->update([
            'closed_at' => now(),
            'closing_cash' => $request->closing_cash,
            'notes' => $request->notes,
        ]);
        
        return redirect()->route('dashboard')
            ->with('success', 'Shift closed successfully.');
    }

    /**
     * Show shift history.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        return Inertia::render('shifts/Index', [
            'shifts' => $user->shifts()
                ->latest()
                ->paginate(20),
        ]);
    }
}
