<!DOCTYPE html>
<html>
<head>
    <title>Redirecting to PayU...</title>
</head>
<body onload="document.forms['payuForm'].submit();">
    <div style="text-align: center; margin-top: 100px; font-family: 'Outfit', sans-serif;">
        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a4/Amazon_Pay_logo.svg/2560px-Amazon_Pay_logo.svg.png" height="30" style="margin-bottom: 20px;">
        <h2>Processing Payment...</h2>
        <p>Please wait while we redirect you to PayU securely.</p>
        
        <form action="{{ $data['baseUrl'] }}" method="post" name="payuForm">
            <input type="hidden" name="key" value="{{ $data['key'] }}" />
            <input type="hidden" name="hash" value="{{ $data['hash'] }}" />
            <input type="hidden" name="txnid" value="{{ $data['txnid'] }}" />
            <input type="hidden" name="amount" value="{{ $data['amount'] }}" />
            <input type="hidden" name="firstname" value="{{ $data['firstname'] }}" />
            <input type="hidden" name="email" value="{{ $data['email'] }}" />
            <input type="hidden" name="phone" value="{{ $data['phone'] }}" />
            <input type="hidden" name="productinfo" value="{{ $data['productinfo'] }}" />
            <input type="hidden" name="surl" value="{{ $data['surl'] }}" />
            <input type="hidden" name="furl" value="{{ $data['furl'] }}" />
            
            <input type="hidden" name="pg" value="{{ $data['pg'] ?? '' }}" />
            <input type="hidden" name="bankcode" value="{{ $data['bankcode'] ?? '' }}" />
            <input type="hidden" name="ccnum" value="{{ $data['ccnum'] ?? '' }}" />
            <input type="hidden" name="ccexpmon" value="{{ $data['ccexpmon'] ?? '' }}" />
            <input type="hidden" name="ccexpyr" value="{{ $data['ccexpyr'] ?? '' }}" />
            <input type="hidden" name="ccvv" value="{{ $data['ccvv'] ?? '' }}" />
            <input type="hidden" name="ccname" value="{{ $data['ccname'] ?? '' }}" />
            <input type="hidden" name="txn_s2s_flow" value="{{ $data['txn_s2s_flow'] ?? '' }}" />

            <!-- UDF fields are essential for hash consistency -->
            <input type="hidden" name="udf1" value="" />
            <input type="hidden" name="udf2" value="" />
            <input type="hidden" name="udf3" value="" />
            <input type="hidden" name="udf4" value="" />
            <input type="hidden" name="udf5" value="" />
            <input type="hidden" name="udf6" value="" />
            <input type="hidden" name="udf7" value="" />
            <input type="hidden" name="udf8" value="" />
            <input type="hidden" name="udf9" value="" />
            <input type="hidden" name="udf10" value="" />
            
            <noscript>
                <p>If you are not redirected within 5 seconds, please click the button below:</p>
                <button type="submit">Pay Now</button>
            </noscript>
        </form>
    </div>
</body>
</html>
