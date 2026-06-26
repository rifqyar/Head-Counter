<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $data['subject'] ?? 'Contact inquiry' }}</title>
</head>
<body style="font-family:Arial,Helvetica,sans-serif;color:#1e293b;background:#f8fafc;margin:0;padding:24px;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:0 auto;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e2e8f0;">
        <tr>
            <td style="background:#0f766e;color:#fff;padding:18px 24px;font-size:16px;font-weight:700;">
                {{ $data['type'] === 'register' ? 'New registration request' : 'New contact inquiry' }}
            </td>
        </tr>
        <tr>
            <td style="padding:24px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;line-height:1.6;">
                    <tr><td style="color:#64748b;width:120px;vertical-align:top;">Name</td><td style="font-weight:600;">{{ $data['name'] }}</td></tr>
                    <tr><td style="color:#64748b;width:120px;vertical-align:top;padding-top:8px;">Email</td><td style="font-weight:600;padding-top:8px;">{{ $data['email'] }}</td></tr>
                    @if (!empty($data['hotel']))
                        <tr><td style="color:#64748b;width:120px;vertical-align:top;padding-top:8px;">Hotel</td><td style="font-weight:600;padding-top:8px;">{{ $data['hotel'] }}</td></tr>
                    @endif
                    @if (!empty($data['plan_label']))
                        <tr><td style="color:#64748b;width:120px;vertical-align:top;padding-top:8px;">Plan</td><td style="font-weight:600;padding-top:8px;">{{ $data['plan_label'] }}</td></tr>
                    @endif
                    <tr><td style="color:#64748b;width:120px;vertical-align:top;padding-top:8px;">Subject</td><td style="font-weight:600;padding-top:8px;">{{ $data['subject'] }}</td></tr>
                    <tr><td style="color:#64748b;width:120px;vertical-align:top;padding-top:8px;">Inquiry type</td><td style="text-transform:capitalize;padding-top:8px;">{{ $data['type'] }}</td></tr>
                    <tr><td style="color:#64748b;width:120px;vertical-align:top;padding-top:8px;">IP</td><td style="padding-top:8px;">{{ $data['ip'] }}</td></tr>
                </table>

                <p style="margin:18px 0 6px;color:#64748b;font-size:12px;text-transform:uppercase;letter-spacing:.05em;">Message</p>
                <div style="background:#f1f5f9;border-radius:8px;padding:14px 16px;white-space:pre-wrap;font-size:14px;line-height:1.6;">{{ $data['message'] }}</div>

                <p style="margin:24px 0 0;font-size:12px;color:#94a3b8;">Sent from the Head Counter landing page at {{ $data['sent_at'] }}.</p>
            </td>
        </tr>
    </table>
</body>
</html>