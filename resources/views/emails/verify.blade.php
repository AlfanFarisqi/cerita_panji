<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            padding-bottom: 20px;
        }

        .content {
            font-size: 16px;
            line-height: 1.5;
        }

        .button {
            display: inline-block;
            background: #007BFF;
            color: #ffffff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>verifikasi</h2>
        </div>
        <div class="content">
            <p>Halo {{ $user->nama }},</p>
            <p>Selamat datang di dunia penuh keajaiban! ✨ Untuk memulai petualangan Anda menjelajahi dongeng-dongeng ajaib, silakan verifikasi akun Anda dengan mengklik tombol di bawah ini:</p>
            <p>

                <a href="{{ $link }}" class="button">Verifikasi Email</a>
            </p>
            <p>Jika Anda tidak mendaftarkan akun di Cerita Panji Kediri, silakan abaikan email ini. Selamat membaca dan selamat berpetualang! 🌠</p>

        </div>
        <div class="footer">
            <p>&copy; 2025 Nama Bisnis Anda. Semua Hak Dilindungi.</p>
        </div>
    </div>
</body>

</html>
