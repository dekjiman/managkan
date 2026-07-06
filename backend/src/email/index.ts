import nodemailer from 'nodemailer'
import { env } from '../config/env'

const transporter = nodemailer.createTransport({
  host: env.SMTP_HOST,
  port: env.SMTP_PORT,
  secure: false,
  auth: {
    user: env.SMTP_USER,
    pass: env.SMTP_PASS,
  },
})

export async function sendVerificationEmail(email: string, url: string) {
  await transporter.sendMail({
    from: `"${env.MAIL_FROM_NAME}" <${env.SMTP_USER}>`,
    to: email,
    subject: 'Verify your email address',
    html: `<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; padding: 32px;">
  <h2>Verify your email</h2>
  <p>Click the button below to verify your email address.</p>
  <a href="${url}" style="display:inline-block;padding:12px 24px;background:#2563eb;color:#fff;text-decoration:none;border-radius:6px;">Verify Email</a>
  <p style="margin-top:24px;color:#666;font-size:14px;">Or copy this link: <br>${url}</p>
</body>
</html>`,
  })
}

export async function sendResetPasswordEmail(email: string, url: string) {
  await transporter.sendMail({
    from: `"${env.MAIL_FROM_NAME}" <${env.SMTP_USER}>`,
    to: email,
    subject: 'Reset your password',
    html: `<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; padding: 32px;">
  <h2>Reset your password</h2>
  <p>Click the button below to set a new password for your account.</p>
  <a href="${url}" style="display:inline-block;padding:12px 24px;background:#2563eb;color:#fff;text-decoration:none;border-radius:6px;">Reset Password</a>
  <p style="margin-top:24px;color:#666;font-size:14px;">If you didn't request a password reset, you can safely ignore this email.</p>
  <p style="color:#666;font-size:14px;">Or copy this link: <br>${url}</p>
</body>
</html>`,
  })
}

export async function sendInviteEmail(email: string, inviterName: string, inviteCode: string) {
  const signupUrl = `${env.FRONTEND_URL}/invite/${inviteCode}`
  await transporter.sendMail({
    from: `"${env.MAIL_FROM_NAME}" <${env.SMTP_USER}>`,
    to: email,
    subject: `You've been invited to join ${env.MAIL_FROM_NAME}`,
    html: `<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; padding: 32px;">
  <h2>You've been invited!</h2>
  <p><strong>${inviterName}</strong> has invited you to join their workspace on ${env.MAIL_FROM_NAME}.</p>
  <p>Click below to create your account:</p>
  <a href="${signupUrl}" style="display:inline-block;padding:12px 24px;background:#2563eb;color:#fff;text-decoration:none;border-radius:6px;">Create Account</a>
  <p style="margin-top:24px;color:#666;font-size:14px;">Or copy this link: <br>${signupUrl}</p>
</body>
</html>`,
  })
}

export async function sendMemberAddedEmail(email: string, addedByName: string, workspaceName: string) {
  const dashboardUrl = `${env.FRONTEND_URL}/dashboard`
  await transporter.sendMail({
    from: `"${env.MAIL_FROM_NAME}" <${env.SMTP_USER}>`,
    to: email,
    subject: `You've been added to ${workspaceName}`,
    html: `<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; padding: 32px;">
  <h2>You've been added to ${workspaceName}</h2>
  <p><strong>${addedByName}</strong> has added you as a member of <strong>${workspaceName}</strong>.</p>
  <p>You can now access the workspace by logging in.</p>
  <a href="${dashboardUrl}" style="display:inline-block;padding:12px 24px;background:#2563eb;color:#fff;text-decoration:none;border-radius:6px;">Go to Dashboard</a>
</body>
</html>`,
  })
}
