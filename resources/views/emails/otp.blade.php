<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f8f9fa; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;">
    <div style="max-width: 600px; margin: 40px auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);">
        
        <!-- Header -->
        <div style="background: linear-gradient(135deg, #6c5ce7 0%, #a8a5e6 100%); padding: 35px 40px; text-align: center; color: #ffffff;">
            <div style="font-size: 26px; font-weight: 800; letter-spacing: -0.5px; margin-bottom: 5px;">
                In-Shop<span style="color: #a8a5e6;">/</span><span style="font-weight: 400; color: #ffffff; opacity: 0.9;">POS</span>
            </div>
            <div style="font-size: 20px; font-weight: 700; margin-top: 15px; text-transform: uppercase; letter-spacing: 0.5px;">
                IN-SHOP POS
            </div>
            <div style="font-size: 13px; opacity: 0.85; margin-top: 5px; font-weight: 500;">
                Your smart restaurant management platform
            </div>
        </div>

        <!-- Body -->
        <div style="padding: 40px; color: #2d3436; line-height: 1.6; font-size: 15px;">
            <p style="margin-top: 0; margin-bottom: 20px; font-weight: 500;">Dear User,</p>
            
            <p style="margin-bottom: 25px;">
                Your verification code is: 
                <span style="background-color: #3b82f6; color: #ffffff; padding: 4px 8px; font-size: 16px; font-weight: bold; border-radius: 4px; letter-spacing: 0.5px; display: inline-block;">{{ $otp }}</span>
            </p>
            
            <p style="margin-bottom: 25px; color: #636e72;">This code will expire in 10 minutes.</p>
            
            <p style="margin-bottom: 30px; color: #636e72; font-size: 14px;">If you didn't request this code, please ignore this email.</p>
            
            <p style="margin-bottom: 0; font-weight: 500; color: #2d3436;">
                Best regards,<br>
                <span style="color: #6c5ce7; font-weight: 700;">IN-SHOP POS</span>
            </p>
        </div>

        <!-- Footer -->
        <div style="background-color: #1e293b; padding: 30px 40px; text-align: center; color: #ffffff;">
            <div style="font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                IN-SHOP POS
            </div>
            <div style="font-size: 11px; color: #94a3b8;">
                &copy; {{ date('Y') }} All rights reserved.
            </div>
        </div>

    </div>
</body>
</html>
