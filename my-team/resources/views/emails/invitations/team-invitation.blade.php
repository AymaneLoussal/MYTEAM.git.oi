<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Team Invitation</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f4f7f2; color:#1f2937; padding:24px;">
    <div style="max-width:640px; margin:0 auto; background:#ffffff; border-radius:16px; padding:32px; border:1px solid #dbe4d5;">
        <p style="margin:0 0 8px; color:#1d4d2f; font-size:12px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase;">FTMBALL</p>
        <h1 style="margin:0 0 16px; font-size:28px; line-height:1.2;">You have been invited to join {{ $invitation->team->name }}</h1>

        <p style="margin:0 0 16px; font-size:16px; line-height:1.6;">
            Coach {{ $invitation->coach->name }} invited you to join the team on FTMBALL.
        </p>

        <p style="margin:0 0 24px; font-size:16px; line-height:1.6;">
            Open the invitation page to accept or reject it. This link will expire on
            {{ optional($invitation->expires_at)->format('F j, Y \a\t H:i') }}.
        </p>

        <p style="margin:0 0 24px;">
            <a href="{{ $invitationUrl }}" style="display:inline-block; background:#1d4d2f; color:#ffffff; text-decoration:none; padding:14px 22px; border-radius:999px; font-weight:700;">
                Review Invitation
            </a>
        </p>

        <p style="margin:0; font-size:14px; line-height:1.6; color:#4b5563;">
            If you do not have an account yet, register first using <strong>{{ $invitation->email }}</strong>, then sign in and open the invitation link again.
        </p>
    </div>
</body>
</html>
