<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class CustomerAuthController extends Controller
{
    public function checkPhone(Request $request)
    {
        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        $customer = Customer::withTrashed()->where('phone', $phone)->first();
        
        return response()->json([
            'status' => true,
            'exists' => $customer && $customer->pin ? true : false,
            'name' => $customer->name ?? ''
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'pin' => 'required|string|size:4',
            'name' => 'nullable|string'
        ]);

        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        $customer = Customer::withTrashed()->where('phone', $phone)->first();

        // Restore if previously soft-deleted
        if ($customer && $customer->trashed()) {
            $customer->restore();
        }

        if ($customer && $customer->pin) {
            // Existing Customer - Verify PIN
            if (!Hash::check($request->pin, $customer->pin)) {
                return response()->json(['status' => false, 'message' => 'Invalid PIN!'], 401);
            }
        } else {
            // New Customer or Existing without PIN - Register/Update PIN
            if (!$customer) {
                $customer = new Customer();
                $customer->phone = $phone;
            }
            $customer->name = $request->name ?? ($customer->name ?? 'User ' . substr($phone, -4));
            $customer->pin = Hash::make($request->pin);
            $customer->device_token = Str::random(60);
            $customer->save();
        }

        // Generate/Return Device Token
        if (!$customer->device_token) {
            $customer->device_token = Str::random(60);
            $customer->save();
        }

        Session::put('customer_id', $customer->id);
        Session::save();

        return response()->json([
            'status' => true,
            'message' => 'Welcome back!',
            'customer' => $customer,
            'device_token' => $customer->device_token
        ]);
    }

    public function autoLogin(Request $request)
    {
        $token = $request->device_token;
        if (!$token) return response()->json(['status' => false]);

        $customer = Customer::where('device_token', $token)->first();
        if ($customer) {
            Session::put('customer_id', $customer->id);
            Session::save();
            return response()->json(['status' => true, 'customer' => $customer]);
        }

        return response()->json(['status' => false]);
    }

    public function myOrders(Request $request)
    {
        $customerId = Session::get('customer_id');

        if (!$customerId) {
            if ($request->ajax()) return response()->json(['error' => 'Unauthorized'], 401);
            return redirect()->route('home')->with('error', 'Please login to see your orders.');
        }

        $customer = Customer::find($customerId);
        $orders = Order::where('customer_id', $customerId)
            ->with([
                'items.item' => fn($q) => $q->withTrashed(),
                'items.variant',
                'items.extras.extra'
            ])
            ->orderBy('id', 'DESC')
            ->paginate(12); // Divisible by 2, 3, 4 for grid consistency

        if ($request->ajax()) {
            return view('customer.partials.order_cards', compact('orders'))->render();
        }

        return view('customer.orders', compact('customer', 'orders'));
    }

    public function reorder($order_number)
    {
        $customerId = Session::get('customer_id');
        if (!$customerId) return redirect()->route('home');

        $order = Order::where('order_number', $order_number)
            ->where('customer_id', $customerId)
            ->with([
                'items.item' => fn($q) => $q->withTrashed(),
                'items.variant',
                'items.extras.extra'
            ])
            ->firstOrFail();

        $cart = [];
        foreach ($order->items as $item) {
            $extras = [];
            foreach ($item->extras as $extra) {
                $extras[] = [
                    'id' => $extra->extra_id,
                    'name' => $extra->extra->name ?? 'Extra',
                    'price' => (float)$extra->price
                ];
            }

            $cart[] = [
                'item_id' => $item->item_id,
                'variant_id' => $item->variant_id,
                'name' => $item->item->name ?? 'Unknown',
                'variant_name' => $item->variant->name ?? null,
                'price' => (float)$item->price,
                'qty' => $item->quantity,
                'extras' => $extras
            ];
        }

        Session::put('reorder_cart', json_encode($cart));
        return redirect()->route('home')->with('success', 'Items added to cart!');
    }

    public function logout()
    {
        $customerId = Session::get('customer_id');
        if ($customerId) {
            Customer::where('id', $customerId)->update(['device_token' => null]);
        }
        Session::forget('customer_id');
        return redirect()->route('home')->with('success', 'Logged out successfully');
    }
}
