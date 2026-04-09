<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentAttempt;
use Illuminate\Support\Facades\Log;

use App\Models\PaymentGateway;
use App\Models\Tenant;

class PayUController extends Controller
{
    private function getSettings()
    {
        $tenant = app()->bound('tenant') ? app('tenant') : Tenant::first();
        $gateway = PaymentGateway::where('tenant_id', $tenant->id)
            ->where('gateway_name', 'PayU')
            ->first();

        if ($gateway && $gateway->is_active && !empty($gateway->settings)) {
            $s = $gateway->settings;
            return [
                'key' => $s['key'] ?? config('services.payu.key'),
                'salt' => $s['salt'] ?? config('services.payu.salt'),
                'mode' => $s['mode'] ?? 'test',
                'base_url' => ($s['mode'] ?? 'test') === 'live' 
                    ? 'https://secure.payu.in/_payment' 
                    : 'https://test.payu.in/_payment'
            ];
        }

        // Fallback to config
        return [
            'key' => config('services.payu.key'),
            'salt' => config('services.payu.salt'),
            'mode' => 'test',
            'base_url' => config('services.payu.base_url', 'https://test.payu.in/_payment')
        ];
    }

    public function pay(Request $request, $order_number)
    {
        Log::info("PayU: Initiating payment for Order #$order_number");

        $order = Order::with('customer')->where('order_number', $order_number)->firstOrFail();

        $settings = $this->getSettings();
        $key = $settings['key'];
        $salt = $settings['salt'];
        $baseUrl = $settings['base_url'];

        // Log environment config (masked)
        Log::info("PayU Config: Key=" . substr($key, 0, 3) . "..." . substr($key, -3) . ", Salt=" . substr($salt, 0, 3) . "..." . substr($salt, -3) . ", BaseURL=" . $baseUrl);

        // Use a shorter txnid (PayU Biz limit is 25 chars)
        $txnid = (string) substr($order->order_number, 0, 20) . 'T' . substr(time(), -4);
        $amount = number_format($order->grand_total, 2, '.', '');
        $productinfo = "ORD" . substr(preg_replace('/[^a-zA-Z0-9]/', '', $order->order_number), -10);
        $firstname = preg_replace('/[^a-zA-Z]/', '', $order->customer->name ?? 'Customer');
        if (strlen($firstname) < 2)
            $firstname = "Customer";

        $email = trim($order->customer->email ?? ($order->customer->phone . '@shop.com'));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            $email = 'customer@shop.com';

        $phone = trim($order->customer->phone ?? '9999999999');
        if (strlen($phone) < 10)
            $phone = '9999999999';

        // Dynamic base URL for success/failure callbacks
        $domain = request()->getSchemeAndHttpHost();
        $surl = $domain . "/payu/success";
        $furl = $domain . "/payu/failure";

        // Correct PayU Hash Sequence: key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10|salt
        $udf1 = $udf2 = $udf3 = $udf4 = $udf5 = $udf6 = $udf7 = $udf8 = $udf9 = $udf10 = '';

        $hashString = $key . '|' . $txnid . '|' . $amount . '|' . $productinfo . '|' . $firstname . '|' . $email . '|' . $udf1 . '|' . $udf2 . '|' . $udf3 . '|' . $udf4 . '|' . $udf5 . '|' . $udf6 . '|' . $udf7 . '|' . $udf8 . '|' . $udf9 . '|' . $udf10 . '|' . $salt;
        $hash = strtolower(hash('sha512', $hashString));

        $data = [
            'key' => (string) $key,
            'txnid' => (string) $txnid,
            'amount' => (string) $amount,
            'productinfo' => (string) $productinfo,
            'firstname' => (string) $firstname,
            'email' => (string) $email,
            'phone' => (string) $phone,
            'surl' => $surl,
            'furl' => $furl,
            'hash' => $hash,
            'pg' => '',
            'bankcode' => '',
            'ccnum' => '',
            'ccexpmon' => '',
            'ccexpyr' => '',
            'ccvv' => '',
            'ccname' => '',
            'txn_s2s_flow' => '',
            'baseUrl' => $baseUrl
        ];

        // If payment method is UPI, use PayU's UPI Intent flow
        if (request('payment_method') === 'UPI' || request('payment_method') === 'PayU') {
            $data['pg'] = 'UPI';
            $data['bankcode'] = 'UPI';
        }

        Log::info("PayU Outgoing Hash String: " . $hashString);
        Log::info("PayU Generated Hash: " . $hash);
        Log::info("PayU Outgoing Request Data: ", array_merge($data, ['key' => 'HIDDEN', 'hash' => 'HIDDEN']));
        
        // Log Attempt in DB
        PaymentAttempt::create([
            'order_id' => $order->id,
            'txnid' => $txnid,
            'status' => 'Initiated',
            'amount' => $amount,
            'hash_string' => $hashString,
            'calculated_hash' => $hash,
            'request_data' => $data
        ]);

        return view('payu.redirect', compact('data'));
    }

    public function success(Request $request)
    {
        $data = $request->all();
        Log::info("PayU: Received Success Callback", $data);

        $settings = $this->getSettings();
        $salt = $settings['salt'];

        if (empty($data) || !isset($data['status'])) {
            Log::warning("PayU: Success callback called with empty data or no status.");
            return redirect()->route('home')->with('info', 'Redirected from payment.');
        }

        // Verification Hash Sequence: salt|status|udf10|udf9|udf8|udf7|udf6|udf5|udf4|udf3|udf2|udf1|email|firstname|productinfo|amount|txnid|key
        $status = $data['status'] ?? '';
        $firstname = $data['firstname'] ?? '';
        $amount = $data['amount'] ?? '';
        $txnid = $data['txnid'] ?? '';
        $productinfo = $data['productinfo'] ?? '';
        $email = $data['email'] ?? '';
        $key = $data['key'] ?? '';

        $udf1 = $data['udf1'] ?? '';
        $udf2 = $data['udf2'] ?? '';
        $udf3 = $data['udf3'] ?? '';
        $udf4 = $data['udf4'] ?? '';
        $udf5 = $data['udf5'] ?? '';
        $udf6 = $data['udf6'] ?? '';
        $udf7 = $data['udf7'] ?? '';
        $udf8 = $data['udf8'] ?? '';
        $udf9 = $data['udf9'] ?? '';
        $udf10 = $data['udf10'] ?? '';

        $retHashString = $salt . '|' . $status . '|' . $udf10 . '|' . $udf9 . '|' . $udf8 . '|' . $udf7 . '|' . $udf6 . '|' . $udf5 . '|' . $udf4 . '|' . $udf3 . '|' . $udf2 . '|' . $udf1 . '|' . $email . '|' . $firstname . '|' . $productinfo . '|' . $amount . '|' . $txnid . '|' . $key;

        $hash = strtolower(hash('sha512', $retHashString));

        Log::info("PayU Calculation: Salt=" . substr($salt, 0, 3) . "..." . substr($salt, -3));
        Log::info("PayU Incoming Hash String: " . $retHashString);
        Log::info("PayU Calculated Hash: " . $hash);
        Log::info("PayU Received Hash: " . ($data['hash'] ?? 'MISSING'));

        if (isset($data['hash']) && (string) $hash === (string) $data['hash'] && ($status == 'success' || $status == 'completed')) {
            Log::info("PayU: Hash verified. Processing order completion.");

            $orderNumber = explode('T', (string) $txnid)[0];
            $order = Order::where('order_number', $orderNumber)->first();

            if (!$order) {
                Log::error("PayU Error: Order not found for Number: " . $orderNumber);
                return redirect()->route('home')->with('error', 'Order not found.');
            }

            $order->payment_status = 'Paid';
            $order->status = 'Preparing';
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

            Log::info("PayU: Order #$orderNumber marked as Paid. Transaction ID: " . ($data['mihpayid'] ?? $txnid));
            
            // Log Attempt as Success
            PaymentAttempt::where('txnid', $txnid)->update([
                'status' => 'Success',
                'mihpayid' => $data['mihpayid'] ?? null,
                'received_hash' => $data['hash'] ?? null,
                'response_data' => $data
            ]);

            return redirect()->route('home.orderSuccess', $order->order_number)->with('success', 'Payment Successful!');
        }
        
        // Log Attempt as Mismatch
        PaymentAttempt::where('txnid', $txnid)->update([
            'status' => 'Hash Mismatch',
            'received_hash' => $data['hash'] ?? null,
            'response_data' => $data,
            'error_message' => 'Calculated hash: ' . $hash . ' | Received: ' . ($data['hash'] ?? 'None')
        ]);

        Log::error("PayU: Hash Mismatch or Failed Status. Status: $status, Calculated Hash: $hash, Received Hash: " . ($data['hash'] ?? 'NONE'));
        return redirect()->route('payu.failure', $data)->with('error', 'Payment verification failed.');
    }

    /**
     * PayU S2S (Server-to-Server) Webhook / Postback
     */
    public function webhook(Request $request)
    {
        $data = $request->all();
        Log::info("PayU S2S: Received Webhook Callback", $data);

        $settings = $this->getSettings();
        $salt = $settings['salt'];

        if (empty($data) || !isset($data['status'])) {
            return response('Invalid Data', 400);
        }

        $status = $data['status'] ?? '';
        $firstname = $data['firstname'] ?? '';
        $amount = $data['amount'] ?? '';
        $txnid = $data['txnid'] ?? '';
        $productinfo = $data['productinfo'] ?? '';
        $email = $data['email'] ?? '';
        $key = $data['key'] ?? '';

        $udf1 = $data['udf1'] ?? '';
        $udf2 = $data['udf2'] ?? '';
        $udf3 = $data['udf3'] ?? '';
        $udf4 = $data['udf4'] ?? '';
        $udf5 = $data['udf5'] ?? '';
        $udf6 = $data['udf6'] ?? '';
        $udf7 = $data['udf7'] ?? '';
        $udf8 = $data['udf8'] ?? '';
        $udf9 = $data['udf9'] ?? '';
        $udf10 = $data['udf10'] ?? '';

        $retHashString = $salt . '|' . $status . '|' . $udf10 . '|' . $udf9 . '|' . $udf8 . '|' . $udf7 . '|' . $udf6 . '|' . $udf5 . '|' . $udf4 . '|' . $udf3 . '|' . $udf2 . '|' . $udf1 . '|' . $email . '|' . $firstname . '|' . $productinfo . '|' . $amount . '|' . $txnid . '|' . $key;
        $hash = strtolower(hash('sha512', $retHashString));

        if (isset($data['hash']) && (string) $hash === (string) $data['hash'] && ($status == 'success' || $status == 'completed')) {
            $orderNumber = explode('T', (string) $txnid)[0];
            $order = Order::where('order_number', $orderNumber)->first();

            if ($order && $order->payment_status !== 'Paid') {
                $order->payment_status = 'Paid';
                $order->status = 'Preparing';
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
                
                PaymentAttempt::where('txnid', $txnid)->update([
                    'status' => 'Success (S2S)',
                    'mihpayid' => $data['mihpayid'] ?? null,
                    'received_hash' => $data['hash'] ?? null,
                    'response_data' => $data
                ]);
                
                Log::info("PayU S2S: Order #$orderNumber marked as Paid via Webhook.");
            }
            return response('OK', 200);
        }

        Log::warning("PayU S2S: Hash Mismatch or Failed Status for Txn: $txnid");
        return response('Hash Mismatch', 400);
    }

    public function failure(Request $request)
    {
        $data = $request->all();
        Log::warning("PayU: Received Failure Callback Data: " . json_encode($data));

        $txnid = $data['txnid'] ?? null;
        $order = null;

        if ($txnid) {
            $orderNumber = explode('T', $txnid)[0];
            $order = Order::where('order_number', $orderNumber)->first();
            if ($order && $order->status === 'Pending Payment') {
                $order->status = 'Payment Failed';
                $order->save();
            }
            Log::info("PayU: Payment failed for Order #$orderNumber. TxnID: $txnid");
        }

        $message = $data['error_Message'] ?? ($data['field9'] ?? ("Your payment was not successful. Status: " . ($data['status'] ?? 'failed')));
        Log::error("PayU Failure Message: " . $message);
        
        // Log Failure in DB
        if ($txnid) {
            PaymentAttempt::where('txnid', $txnid)->update([
                'status' => 'Failed',
                'mihpayid' => $data['mihpayid'] ?? null,
                'response_data' => $data,
                'error_message' => $message
            ]);
        }

        return view('order-failed', compact('message', 'order'));
    }
}
