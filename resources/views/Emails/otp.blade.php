<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your OTP Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            border: 0.5px solid #e5e7eb;
            overflow: hidden;
        }
        .header {
            background: #1e3a5f;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            color: white;
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .otp-code {
            font-size: 36px;
            font-weight: bold;
            letter-spacing: 8px;
            background: #f3f4f6;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            margin: 20px 0;
            font-family: monospace;
        }
        .footer {
            background: #f9fafb;
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            border-top: 0.5px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Khadamati</h1>
            <p style="color: rgba(255,255,255,0.7); margin: 5px 0 0;">Government Services Portal</p>
        </div>
        
        <div class="content">
            <h2 style="margin-top: 0;">Your Verification Code</h2>
            
            <p>Hello <strong>{{ $name }}</strong>,</p>
            
            <p>You requested to log in to your Khadamati account. Please use the verification code below:</p>
            
            <div class="otp-code">
                {{ $otp }}
            </div>
            
            <p>This code will expire in <strong>10 minutes</strong>.</p>
            
            <p style="color: #6b7280; font-size: 13px;">
                If you didn't request this, please ignore this email.
            </p>
        </div>
        
        <div class="footer">
            <p>&copy; {{ date('Y') }} Khadamati. All rights reserved.</p>
            <p>Secure government services platform</p>
        </div>
    </div>
</body>
</html>