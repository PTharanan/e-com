<!DOCTYPE html>
<html>
<head>
    <title>Registration OTP</title>
</head>
<body>
    <h2>Hello {{ $name }},</h2>
    <p>Thank you for signing up. Your One-Time Password (OTP) is:</p>
    <h1 style="color: #F25C3B; letter-spacing: 2px;">{{ $otp }}</h1>
    <p>This OTP is valid for 60 seconds.</p>
    <p>If you did not request this, please ignore this email.</p>
</body>
</html>
