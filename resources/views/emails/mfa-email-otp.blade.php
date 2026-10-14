<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kode Verifikasi</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5;">
    <h2 style="margin-bottom: 8px;">Kode Verifikasi Masuk</h2>
    <p style="margin-top: 0;">Gunakan kode berikut untuk menyelesaikan verifikasi di Raksantara.</p>

    <p style="font-size: 28px; font-weight: bold; letter-spacing: 6px; font-family: 'Courier New', monospace; margin: 16px 0;">{{ $code }}</p>

    <p>Kode berlaku selama {{ $ttlMinutes }} menit dan hanya bisa dipakai sekali.</p>
    <p style="color: #6b7280; font-size: 13px;">Jika Anda tidak sedang masuk ke aplikasi, abaikan email ini dan segera ganti password Anda. Jangan bagikan kode ini kepada siapa pun.</p>
</body>
</html>
