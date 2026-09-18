<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Reset Your Password — Ravellen</title>
</head>
<body style="margin:0;padding:0;background:#f5f3ff;font-family:'Inter',Arial,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f3ff;padding:2.5rem 1rem;">
    <tr>
        <td align="center">
            <table width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#fff;border-radius:1rem;box-shadow:0 4px 32px rgba(124,58,237,.10);overflow:hidden;">

                
                <tr>
                    <td style="background:linear-gradient(135deg,#ec4899,#a855f7);padding:2rem;text-align:center;">
                        <h1 style="color:#ffffff;font-size:2rem;font-weight:800;margin:0 0 .5rem;letter-spacing:.02em;">Ravellen</h1>
                        <p style="color:rgba(255,255,255,.9);font-size:.9rem;margin:0;">Your mystery adventure awaits 💜</p>
                    </td>
                </tr>

                
                <tr>
                    <td style="padding:2.5rem 2rem;text-align:center;">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td align="center" style="padding-bottom:1.25rem;">
                                    <div style="width:3.5rem;height:3.5rem;background:#fce7f3;border-radius:50%;text-align:center;line-height:3.5rem;font-size:1.5rem;display:inline-block;">🔐</div>
                                </td>
                            </tr>
                        </table>

                        <h2 style="font-size:1.4rem;font-weight:800;color:#1f2937;margin:0 0 .75rem;">
                            Reset Your Password
                        </h2>
                        <p style="color:#6b7280;font-size:.9rem;line-height:1.6;margin:0 0 2rem;">
                            We received a request to reset the password for your Ravellen account.
                            Click the button below to set a new password. This link expires in <strong>60 minutes</strong>.
                        </p>

                        
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td align="center" style="padding-bottom:2rem;">
                                    <a href="<?php echo e($resetUrl); ?>"
                                       style="display:inline-block;background:linear-gradient(135deg,#ec4899,#a855f7);color:#fff;font-weight:700;font-size:1rem;text-decoration:none;padding:.85rem 2.5rem;border-radius:.6rem;letter-spacing:.01em;">
                                        Reset Password
                                    </a>
                                </td>
                            </tr>
                        </table>

                        <p style="color:#9ca3af;font-size:.8rem;text-align:center;margin:0 0 1rem;">
                            If the button doesn't work, copy and paste this link into your browser:
                        </p>
                        <p style="word-break:break-all;font-size:.75rem;color:#6366f1;text-align:center;margin:0 0 2rem;">
                            <?php echo e($resetUrl); ?>

                        </p>

                        <div style="background:#fef9ff;border:1px solid #e9d5ff;border-radius:.5rem;padding:.85rem 1rem;font-size:.8rem;color:#6b7280;text-align:center;">
                            If you didn't request a password reset, you can safely ignore this email.
                            Your password will not change.
                        </div>
                    </td>
                </tr>

                
                <tr>
                    <td style="background:#f9fafb;padding:1.25rem 2rem;text-align:center;border-top:1px solid #f3f4f6;">
                        <p style="color:#9ca3af;font-size:.75rem;margin:0;">
                            © <?php echo e(date('Y')); ?> Ravellen. All rights reserved.
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
<?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/emails/password-reset.blade.php ENDPATH**/ ?>