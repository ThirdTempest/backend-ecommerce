<!DOCTYPE html>
<html>
<head>
    <title>Verification Code</title>
</head>
<body style="font-family: Arial, sans-serif; text-align: center; padding: 20px;">
    <h2>Welcome to E-SHOP!</h2>
    <p>Please use the following One-Time Password (OTP) to verify your account:</p>

    <div style="margin: 20px auto; padding: 15px; background-color: #f4f4f4; display: inline-block; font-size: 24px; font-weight: bold; letter-spacing: 5px; border-radius: 8px;">
        {{ $otp }}
    </div>

    <p>This code is valid for 10 minutes.</p>
    <p>If you did not register for an account, please ignore this email.</p>
</body>
</html>
