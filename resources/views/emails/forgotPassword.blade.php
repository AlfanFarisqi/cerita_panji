<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Kata Sandi</title>
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
            background: #28a745;
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
            <h2>Reset Kata Sandi</h2>
        </div>
        <div class="content">
            <p>Halo {{ $user->nama }},</p>
            <p>Kami menerima permintaan untuk mereset kata sandi akun Anda. Jika Anda ingin melanjutkan, silakan klik tombol di bawah ini:</p>
            <p>
                <a href="{{ $link }}" class="button">Reset Kata Sandi</a>
            </p>
            <p>Jika Anda tidak meminta reset ini, abaikan email ini. Demi keamanan akun Anda, jangan bagikan tautan ini kepada siapa pun.</p>
        </div>
        <div class="footer">
            <p>&copy; 2025 Nama Bisnis Anda. Semua Hak Dilindungi.</p>
        </div>
    </div>
</body>

</html>
