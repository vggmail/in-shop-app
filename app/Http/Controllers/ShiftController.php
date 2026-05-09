<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\PettyCash;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::with('user')->orderBy('id', 'desc')->paginate(15);
        return view('admin.shifts.index', compact('shifts'));
    }

    public function open(Request $request)
    {
        $activeShift = Shift::where('status', 'Open')->first();
        if ($activeShift) {
            return redirect()->back()->with('error', 'A shift is already open.');
        }

        $request->validate([
            'opening_balance' => 'required|numeric|min:0'
        ]);

        Shift::create([
            'user_id' => Auth::id(),
            'opening_balance' => $request->opening_balance,
            'status' => 'Open',
            'opened_at' => Carbon::now()
        ]);

        return redirect()->back()->with('success', 'Shift started successfully.');
    }

    public function showCloseForm()
    {
        $shift = Shift::where('status', 'Open')->first();
        if (!$shift) {
            return redirect()->route('dashboard')->with('error', 'No active shift found.');
        }

        // Calculate expected balance
        $cashSales = Order::where('created_at', '>=', $shift->opened_at)
            ->where('payment_method', 'Cash')
            ->where('payment_status', 'Paid')
            ->sum('grand_total');

        $pettyIn = PettyCash::where('shift_id', $shift->id)->where('type', 'In')->sum('amount');
        $pettyOut = PettyCash::where('shift_id', $shift->id)->where('type', 'Out')->sum('amount');

        $expected = $shift->opening_balance + $cashSales + $pettyIn - $pettyOut;

        return view('admin.shifts.close', compact('shift', 'cashSales', 'pettyIn', 'pettyOut', 'expected'));
    }

    public function close(Request $request)
    {
        $shift = Shift::where('status', 'Open')->first();
        if (!$shift) {
            return redirect()->route('dashboard')->with('error', 'No active shift found.');
        }

        $request->validate([
            'closing_balance' => 'required|numeric|min:0'
        ]);

        // Re-calculate expected for final record
        $cashSales = Order::where('created_at', '>=', $shift->opened_at)
            ->where('payment_method', 'Cash')
            ->where('payment_status', 'Paid')
            ->sum('grand_total');

        $pettyIn = PettyCash::where('shift_id', $shift->id)->where('type', 'In')->sum('amount');
        $pettyOut = PettyCash::where('shift_id', $shift->id)->where('type', 'Out')->sum('amount');

        $expected = $shift->opening_balance + $cashSales + $pettyIn - $pettyOut;

        $shift->update([
            'closing_balance' => $request->closing_balance,
            'expected_balance' => $expected,
            'status' => 'Closed',
            'closed_at' => Carbon::now()
        ]);

        return redirect()->route('dashboard')->with('success', 'Shift closed and balanced.');
    }

    public function storePettyCash(Request $request)
    {
        $shift = Shift::where('status', 'Open')->first();
        if (!$shift) {
            return redirect()->back()->with('error', 'Open a shift first to record petty cash.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'type' => 'required|in:In,Out',
            'reason' => 'required|string|max:255'
        ]);

        PettyCash::create([
            'shift_id' => $shift->id,
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'type' => $request->type,
            'reason' => $request->reason
        ]);

        return redirect()->back()->with('success', 'Petty cash log saved.');
    }
}
