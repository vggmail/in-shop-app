<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

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
        $throttleKey = 'customer-login:' . $phone;

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json(['status' => false, 'message' => "Too many failed attempts. Please try again in $seconds seconds."], 429);
        }

        $customer = Customer::withTrashed()->where('phone', $phone)->first();

        // Restore if previously soft-deleted
        if ($customer && $customer->trashed()) {
            $customer->restore();
        }

        if ($customer && $customer->pin) {
            // Existing Customer - Verify PIN
            if (!Hash::check($request->pin, $customer->pin)) {
                RateLimiter::hit($throttleKey, 60); // 1 minute lockout after 5 fails
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

        RateLimiter::clear($throttleKey);

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
        Session::forget('customer_id');
        return redirect()->route('home')->with('success', 'Logged out successfully');
    }

    public function updatePin(Request $request)
    {
        $customerId = Session::get('customer_id');
        if (!$customerId) return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);

        $request->validate([
            'phone_verify' => 'required|string|size:10',
            'new_pin' => 'required|string|size:4|confirmed',
        ]);

        $customer = Customer::find($customerId);
        if ($request->phone_verify !== $customer->phone) {
            return response()->json(['status' => false, 'message' => 'Mobile number does not match!'], 422);
        }

        $customer->pin = Hash::make($request->new_pin);
        $customer->save();

        return response()->json(['status' => true, 'message' => 'PIN updated successfully!']);
    }

    public function sendForgotPinOtp(Request $request)
    {
        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        $throttleKey = 'otp-send:' . $phone;

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return response()->json(['status' => false, 'message' => "Too many OTP requests. Please wait $seconds seconds before trying again."], 429);
        }

        $customer = Customer::where('phone', $phone)->first();
        if (!$customer) return response()->json(['status' => false, 'message' => 'Number not registered!'], 404);

        RateLimiter::hit($throttleKey, 300); // 5 minutes throttle for 3 attempts

        $otp = rand(1000, 9999);
        Cache::put('otp_' . $phone, $otp, now()->addMinutes(10));

        // Mock WhatsApp Sending - Log it for now
        Log::info("OTP for $phone: $otp");
        
        // For real WhatsApp integration:
        // $this->sendWhatsAppOtp($phone, $otp);

        return response()->json(['status' => true, 'message' => 'OTP sent successfully!']);
    }

    public function verifyForgotOtp(Request $request)
    {
        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        $cachedOtp = Cache::get('otp_' . $phone);

        if ($cachedOtp && $cachedOtp == $request->otp) {
            return response()->json(['status' => true]);
        }

        return response()->json(['status' => false, 'message' => 'Invalid or expired OTP!'], 422);
    }

    public function resetForgotPin(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'otp' => 'required',
            'pin' => 'required|string|size:4|confirmed'
        ]);

        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        $cachedOtp = Cache::get('otp_' . $phone);

        if (!$cachedOtp || $cachedOtp != $request->otp) {
            return response()->json(['status' => false, 'message' => 'OTP expired or invalid!'], 422);
        }

        $customer = Customer::where('phone', $phone)->firstOrFail();
        $customer->pin = Hash::make($request->pin);
        $customer->save();

        Cache::forget('otp_' . $phone);

        return response()->json(['status' => true, 'message' => 'PIN reset successfully!']);
    }

    private function sendWhatsAppOtp($phone, $otp)
    {
        // Add your Meta Cloud API logic here
        // $token = env('WHATSAPP_TOKEN');
        // $phoneId = env('WHATSAPP_PHONE_ID');
        // $url = "https://graph.facebook.com/v17.0/$phoneId/messages";
    }

    public function profile()
    {
        $customerId = Session::get('customer_id');
        if (!$customerId) return redirect()->route('home');

        $customer = Customer::with('addresses')->find($customerId);
        return view('customer.profile', compact('customer'));
    }

    public function saveAddress(Request $request)
    {
        $customerId = Session::get('customer_id');
        if (!$customerId) return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);

        $request->validate([
            'street_address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|max:10',
            'label' => 'required|string|max:50',
            'is_default' => 'nullable|boolean'
        ]);

        if ($request->is_default) {
            \App\Models\CustomerAddress::where('customer_id', $customerId)->update(['is_default' => false]);
        }

        $address = \App\Models\CustomerAddress::updateOrCreate(
            ['id' => $request->id, 'customer_id' => $customerId],
            [
                'street_address' => $request->street_address,
                'city' => $request->city,
                'state' => $request->state,
                'pincode' => $request->pincode,
                'label' => $request->label,
                'is_default' => $request->is_default ?? false
            ]
        );

        return response()->json(['status' => true, 'message' => 'Address saved successfully', 'address' => $address]);
    }

    public function deleteAddress($id)
    {
        $customerId = Session::get('customer_id');
        if (!$customerId) return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);

        \App\Models\CustomerAddress::where('id', $id)->where('customer_id', $customerId)->delete();
        return response()->json(['status' => true, 'message' => 'Address deleted successfully']);
    }

    public function setDefaultAddress($id)
    {
        $customerId = Session::get('customer_id');
        if (!$customerId) return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);

        \App\Models\CustomerAddress::where('customer_id', $customerId)->update(['is_default' => false]);
        \App\Models\CustomerAddress::where('id', $id)->where('customer_id', $customerId)->update(['is_default' => true]);

        return response()->json(['status' => true, 'message' => 'Default address set successfully']);
    }

    public function updateProfile(Request $request)
    {
        $customerId = Session::get('customer_id');
        if (!$customerId) return response()->json(['status' => false, 'message' => 'Unauthorized'], 401);

        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:150',
        ]);

        $customer = Customer::find($customerId);
        $customer->name = $request->name;
        $customer->email = $request->email;
        $customer->save();

        return response()->json(['status' => true, 'message' => 'Profile updated successfully!']);
    }
}
