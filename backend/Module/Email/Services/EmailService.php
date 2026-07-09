<?php

namespace Module\Email\Services;

use CodeIgniter\Email\Email;
use Config\Email as EmailConfig;

class EmailService
{
    protected Email $email;

    public function __construct()
    {
        $this->email = new Email(new EmailConfig());
    }

    public function sendInvite(string $toEmail, string $inviteCode, string $workspaceName, string $inviterName): bool
    {
        $frontendUrl = env('frontend.url', 'http://localhost:5173');
        $inviteLink = $frontendUrl . '/invite/' . $inviteCode;

        $this->email->setTo($toEmail);
        $this->email->setSubject("You've been invited to join {$workspaceName} on ManagPro");

        $html = $this->buildInviteTemplate($workspaceName, $inviterName, $inviteLink);
        $this->email->setMessage($html);

        return $this->email->send();
    }

    public function sendVerification(string $toEmail, string $token): bool
    {
        $frontendUrl = env('frontend.url', 'http://localhost:5173');
        $verifyLink = $frontendUrl . '/verify-email?token=' . $token;

        $this->email->setTo($toEmail);
        $this->email->setSubject('Verify your ManagPro account');

        $html = $this->buildVerificationTemplate($toEmail, $verifyLink);
        $this->email->setMessage($html);

        return $this->email->send();
    }

    private function buildVerificationTemplate(string $email, string $verifyLink): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background-color:#f4f5f7;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f5f7;padding:40px 0;">
        <tr>
            <td align="center">
                <table width="480" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                    <tr>
                        <td style="background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:32px 40px;text-align:center;">
                            <h1 style="margin:0;color:#ffffff;font-size:24px;font-weight:700;">ManagPro</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:40px;">
                            <h2 style="margin:0 0 16px;color:#1a1a2e;font-size:20px;font-weight:600;">Verify your email</h2>
                            <p style="margin:0 0 8px;color:#4a4a68;font-size:15px;line-height:1.6;">
                                Thanks for signing up! Please click the button below to verify your email address.
                            </p>
                            <table cellpadding="0" cellspacing="0" style="width:100%;">
                                <tr>
                                    <td align="center">
                                        <a href="{$verifyLink}" style="display:inline-block;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#ffffff;text-decoration:none;padding:14px 48px;border-radius:8px;font-size:16px;font-weight:600;">Verify Email</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:32px 0 0;color:#9a9ab0;font-size:13px;line-height:1.5;">
                                If the button doesn't work, copy and paste this link into your browser:<br>
                                <a href="{$verifyLink}" style="color:#6366f1;word-break:break-all;">{$verifyLink}</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 40px;background-color:#f8f9fc;border-top:1px solid #eef0f5;">
                            <p style="margin:0;color:#9a9ab0;font-size:12px;text-align:center;">
                                If you didn't create an account, you can ignore this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private function buildInviteTemplate(string $workspaceName, string $inviterName, string $inviteLink): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0;padding:0;background-color:#f4f5f7;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f5f7;padding:40px 0;">
        <tr>
            <td align="center">
                <table width="480" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                    <tr>
                        <td style="background:linear-gradient(135deg,#6366f1,#8b5cf6);padding:32px 40px;text-align:center;">
                            <h1 style="margin:0;color:#ffffff;font-size:24px;font-weight:700;">ManagPro</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:40px;">
                            <h2 style="margin:0 0 16px;color:#1a1a2e;font-size:20px;font-weight:600;">You've been invited!</h2>
                            <p style="margin:0 0 8px;color:#4a4a68;font-size:15px;line-height:1.6;">
                                <strong>{$inviterName}</strong> has invited you to join the workspace <strong>{$workspaceName}</strong> on ManagPro.
                            </p>
                            <p style="margin:0 0 32px;color:#4a4a68;font-size:15px;line-height:1.6;">
                                Click the button below to accept the invitation and create your account.
                            </p>
                            <table cellpadding="0" cellspacing="0" style="width:100%;">
                                <tr>
                                    <td align="center">
                                        <a href="{$inviteLink}" style="display:inline-block;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#ffffff;text-decoration:none;padding:14px 48px;border-radius:8px;font-size:16px;font-weight:600;">Accept Invitation</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:32px 0 0;color:#9a9ab0;font-size:13px;line-height:1.5;">
                                If the button doesn't work, copy and paste this link into your browser:<br>
                                <a href="{$inviteLink}" style="color:#6366f1;word-break:break-all;">{$inviteLink}</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 40px;background-color:#f8f9fc;border-top:1px solid #eef0f5;">
                            <p style="margin:0;color:#9a9ab0;font-size:12px;text-align:center;">
                                This invitation was sent by {$inviterName}. If you didn't expect this, you can ignore this email.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }
}
