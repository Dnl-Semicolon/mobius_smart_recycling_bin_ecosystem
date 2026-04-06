<p>Hi {{ $user->name }},</p>

<p>Welcome to Mobius.</p>

<p>Your 6-digit verification code is:</p>

<p style="font-size: 28px; font-weight: 700; letter-spacing: 0.3em;">{{ $code }}</p>

<p>This code expires in {{ \App\Support\EmailOtpStore::TTL_MINUTES }} minutes.</p>
