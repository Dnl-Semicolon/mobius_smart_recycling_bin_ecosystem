<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify your email</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellpadding="0" cellspacing="0" style="max-width: 560px; width: 100%;">
                    {{-- Logo / Header --}}
                    <tr>
                        <td align="center" style="padding-bottom: 24px;">
                            <div style="display: inline-flex; align-items: center; gap: 10px;">
                                <div style="width: 40px; height: 40px; background-color: #059669; border-radius: 12px; display: inline-block; text-align: center; line-height: 40px;">
                                    <span style="color: #ffffff; font-size: 20px; font-weight: 700;">M</span>
                                </div>
                                <span style="font-size: 22px; font-weight: 700; color: #111827; letter-spacing: -0.5px;">Mobius</span>
                            </div>
                        </td>
                    </tr>

                    {{-- Main card --}}
                    <tr>
                        <td style="background-color: #ffffff; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
                            {{-- Green accent bar --}}
                            <div style="height: 4px; background: linear-gradient(90deg, #059669, #10b981); border-radius: 16px 16px 0 0;"></div>

                            <div style="padding: 40px 36px;">
                                <h1 style="margin: 0 0 8px; font-size: 22px; font-weight: 700; color: #111827;">Verify your email</h1>
                                <p style="margin: 0 0 24px; font-size: 15px; color: #6b7280; line-height: 1.5;">
                                    Hi {{ $request->contact_name }},
                                </p>

                                <p style="margin: 0 0 16px; font-size: 15px; color: #374151; line-height: 1.6;">
                                    Thank you for registering <strong>{{ $request->company_name }}</strong> with Mobius.
                                    Please verify your email address by clicking the button below.
                                </p>

                                {{-- CTA Button --}}
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin: 28px 0;">
                                    <tr>
                                        <td align="center">
                                            <a href="{{ url('/register/verify-email/' . $request->email_verification_token) }}"
                                               style="display: inline-block; background-color: #059669; color: #ffffff; font-size: 15px; font-weight: 600; text-decoration: none; padding: 14px 32px; border-radius: 10px; letter-spacing: 0.2px;">
                                                Verify Email Address
                                            </a>
                                        </td>
                                    </tr>
                                </table>

                                <p style="margin: 0 0 8px; font-size: 13px; color: #9ca3af; line-height: 1.5;">
                                    Or copy and paste this link into your browser:
                                </p>
                                <p style="margin: 0 0 24px; font-size: 13px; color: #059669; word-break: break-all; line-height: 1.5;">
                                    {{ url('/register/verify-email/' . $request->email_verification_token) }}
                                </p>

                                <div style="border-top: 1px solid #f3f4f6; padding-top: 20px; margin-top: 8px;">
                                    <p style="margin: 0; font-size: 13px; color: #9ca3af; line-height: 1.5;">
                                        This link will expire in <strong style="color: #6b7280;">48 hours</strong>.
                                        If you did not register with Mobius, you can safely ignore this email.
                                    </p>
                                </div>
                            </div>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="padding-top: 28px;">
                            <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                                &copy; {{ date('Y') }} Mobius Smart Recycling. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
