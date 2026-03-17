<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: Georgia, 'Segoe UI', sans-serif; background:#f4f0f1; padding:24px; color:#1a1416;">
    <div style="max-width:480px;margin:0 auto;background:#fffefb;border-radius:12px;padding:32px;box-shadow:0 4px 24px rgba(0,0,0,.08);">
        <p style="font-size:1.25rem;font-weight:700;color:#a67b88;margin:0 0 8px;">Rg Bar-restaurant</p>
        <h1 style="font-size:1.125rem;margin:0 0 16px;">{{ __('Password reset code') }}</h1>
        <p style="color:#5c5356;line-height:1.5;margin:0 0 24px;">{{ __('Use this code to set a new password. It expires in :minutes minutes.', ['minutes' => $expiresMinutes]) }}</p>
        <p style="font-size:2rem;letter-spacing:0.35em;font-weight:700;text-align:center;padding:16px 24px;background:linear-gradient(135deg,#f8f4f5,#efe8ea);border-radius:8px;margin:0 0 24px;color:#1a1416;">{{ $otp }}</p>
        <p style="font-size:0.875rem;color:#8a8184;margin:0;">{{ __('If you did not request this, you can ignore this email.') }}</p>
    </div>
</body>
</html>
