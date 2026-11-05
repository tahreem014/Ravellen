<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>New contact message — Ravellen</title>
</head>
<body style="margin:0;padding:0;background:#f5f3ff;font-family:'Inter',Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f3ff;padding:2.5rem 1rem;">
    <tr>
        <td align="center">
            <table width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#fff;border-radius:1rem;box-shadow:0 4px 32px rgba(124,58,237,.10);overflow:hidden;">

                {{-- Header --}}
                <tr>
                    <td style="background:linear-gradient(135deg,#ec4899,#a855f7);padding:1.5rem 2rem;text-align:center;">
                        <h1 style="color:#ffffff;font-size:1.5rem;font-weight:800;margin:0;letter-spacing:.02em;">New contact message</h1>
                    </td>
                </tr>

                {{-- Body --}}
                <tr>
                    <td style="padding:2rem;">
                        <p style="color:#6b7280;font-size:.85rem;margin:0 0 .25rem;">From</p>
                        <p style="color:#1f2937;font-size:1rem;font-weight:600;margin:0 0 .25rem;">{{ $senderName }}</p>
                        <p style="margin:0 0 1.5rem;"><a href="mailto:{{ $senderEmail }}" style="color:#ec4899;font-size:.9rem;text-decoration:none;">{{ $senderEmail }}</a></p>

                        <p style="color:#6b7280;font-size:.85rem;margin:0 0 .5rem;">Message</p>
                        <div style="background:#fef9ff;border:1px solid #e9d5ff;border-radius:.5rem;padding:1rem;color:#1f2937;font-size:.9rem;line-height:1.6;white-space:pre-wrap;">{{ $body }}</div>

                        <p style="color:#9ca3af;font-size:.8rem;margin:1.5rem 0 0;">Hit reply to answer {{ $senderName }} directly.</p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
