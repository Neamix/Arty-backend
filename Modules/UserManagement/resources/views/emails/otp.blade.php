<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verification code</title>
</head>
<body style="font-family: sans-serif; color: #111;">
    <h2>Your verification code</h2>
    <p>Use the code below to continue. It expires in {{ $expiresInMinutes }} minutes.</p>
    <p style="font-size: 28px; letter-spacing: 6px; font-weight: bold;">{{ $otp }}</p>
    <p style="color: #666; font-size: 12px;">Purpose: {{ $usage->value }}. If you did not request this code, you can ignore this email.</p>
</body>
</html>
