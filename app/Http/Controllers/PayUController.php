<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;

class PayUController extends Controller
{
    public function pay(Request $request, $order_number)
    {
        $order = Order::with('customer')->where('order_number', $order_number)->firstOrFail();
        
        $key = env('PAYU_KEY', 'oesh51');
        $salt = env('PAYU_SALT', 'y3BxbGFV22o1Lu16frQ9Bv78BaStu4pA');
        $baseUrl = env('PAYU_BASE_URL', 'https://test.payu.in');
        
        $txnid = $order->order_number;
        $amount = number_format($order->grand_total, 2, '.', '');
        $productinfo = "FoodOrder" . $order->id;
        $firstname = str_replace(' ', '', $order->customer->name ?? 'Customer');
        $email = $order->customer->email ?? ($order->customer->phone . '@shop.com');
        $phone = $order->customer->phone ?? '0000000000';
        
        $surl = route('payu.success');
        $furl = route('payu.failure');
        
        // Correct PayU Hash Sequence: key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10|salt
        $udf1=$udf2=$udf3=$udf4=$udf5=$udf6=$udf7=$udf8=$udf9=$udf10 = '';
        
        $hashString = $key . '|' . $txnid . '|' . $amount . '|' . $productinfo . '|' . $firstname . '|' . $email . '|' . $udf1 . '|' . $udf2 . '|' . $udf3 . '|' . $udf4 . '|' . $udf5 . '|' . $udf6 . '|' . $udf7 . '|' . $udf8 . '|' . $udf9 . '|' . $udf10 . '|' . $salt;
        $hash = strtolower(hash('sha512', $hashString));
        
        $data = [
            'key' => $key,
            'txnid' => $txnid,
            'amount' => $amount,
            'productinfo' => $productinfo,
            'firstname' => $firstname,
            'email' => $email,
            'phone' => $phone,
            'surl' => $surl,
            'furl' => $furl,
            'hash' => $hash,
            'service_provider' => 'payu_paisa',
            'baseUrl' => $baseUrl . '/_payment'
        ];
        
        Log::info("PayU Request Hash String: " . $hashString);
        
        return view('payu.redirect', compact('data'));
    }

    public function success(Request $request)
    {
        $data = $request->all();
        $salt = env('PAYU_SALT');

        if (empty($data) || !isset($data['status'])) {
            // Likely a direct visit or empty redirect
            return redirect()->route('home')->with('info', 'Redirected from payment.');
        }
        
        Log::info("PayU Success Callback Data: ", $data);

        // Verification Hash Sequence: salt|status|udf10|udf9|udf8|udf7|udf6|udf5|udf4|udf3|udf2|udf1|email|firstname|productinfo|amount|txnid|key
        $status = $data['status'];
        $firstname = $data['firstname'];
        $amount = $data['amount'];
        $txnid = $data['txnid'];
        $productinfo = $data['productinfo'];
        $email = $data['email'];
        $key = $data['key'];
        
        $udf1=$udf2=$udf3=$udf4=$udf5=$udf6=$udf7=$udf8=$udf9=$udf10 = ''; // Assuming empty as we sent empty

        $retHashString = $salt . '|' . $status . '|' . $udf10 . '|' . $udf9 . '|' . $udf8 . '|' . $udf7 . '|' . $udf6 . '|' . $udf5 . '|' . $udf4 . '|' . $udf3 . '|' . $udf2 . '|' . $udf1 . '|' . $email . '|' . $firstname . '|' . $productinfo . '|' . $amount . '|' . $txnid . '|' . $key;
        
        $hash = strtolower(hash('sha512', $retHashString));
        
        if ($hash == $data['hash'] && ($status == 'success' || $status == 'completed')) {
            $order = Order::where('order_number', $txnid)->firstOrFail();
            $order->payment_status = 'Paid';
            $order->save();
            
            Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'method' => 'PayU',
                    'amount' => $amount,
                    'date' => date('Y-m-d'),
                    'status' => 'Paid',
                    'transaction_id' => $data['mihpayid'] ?? $txnid
                ]
            );
            
            return redirect()->route('home.orderSuccess', $order->order_number)->with('success', 'Payment Successful!');
        }
        
        Log::error("PayU Hash Mismatch or Failed Status. Calculated: $hash, Received: " . $data['hash']);
        return redirect()->route('payu.failure', ['txnid' => $txnid])->with('error', 'Payment verification failed.');
    }

    public function failure(Request $request)
    {
        $data = $request->all();
        $orderNumber = $data['txnid'] ?? session('order_number');
        $order = null;

        if ($orderNumber) {
            $order = Order::where('order_number', $orderNumber)->first();
        }

        $message = $data['error_Message'] ?? "Your payment was not successful. Status: " . ($data['status'] ?? 'failed');
        
        return view('order-failed', compact('message', 'order'));
    }
}
