<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Scheduled Report Delivered</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5;">
    <h2 style="margin-bottom: 8px;">Scheduled Report Delivered</h2>
    <p style="margin-top: 0;">Report dari Raksantara sudah digenerate otomatis sesuai jadwal.</p>

    <table cellpadding="6" cellspacing="0" border="0" style="border-collapse: collapse; margin-top: 8px;">
        <tr>
            <td><strong>Schedule Name</strong></td>
            <td>{{ $scheduledReport->name }}</td>
        </tr>
        <tr>
            <td><strong>Scope</strong></td>
            <td>{{ strtoupper((string) $scheduledReport->scope) }}</td>
        </tr>
        <tr>
            <td><strong>Frequency</strong></td>
            <td>{{ ucfirst((string) $scheduledReport->frequency) }}</td>
        </tr>
        <tr>
            <td><strong>Generated At</strong></td>
            <td>{{ now()->format('Y-m-d H:i:s') }}</td>
        </tr>
    </table>

    <p>Lampiran report ada pada email ini.</p>
</body>
</html>
