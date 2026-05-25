<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode OTP Reset Password - Quizyfy</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f4f8;
            padding: 20px;
        }
        .wrapper {
            max-width: 600px;
            margin: 0 auto;
        }
        .container {
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .header {
            background: linear-gradient(135deg, #1565C0 0%, #2196F3 50%, #42A5F5 100%);
            padding: 40px 30px;
            text-align: center;
        }
        .header-logo {
            font-size: 32px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: 2px;
            margin-bottom: 4px;
        }
        .header-tagline {
            font-size: 13px;
            color: rgba(255,255,255,0.8);
            letter-spacing: 1px;
        }
        .content {
            padding: 40px 36px;
        }
        .greeting {
            font-size: 20px;
            font-weight: 600;
            color: #1a1a2e;
            margin-bottom: 16px;
        }
        .message {
            font-size: 15px;
            color: #555;
            line-height: 1.7;
            margin-bottom: 32px;
        }
        .otp-section {
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            border-radius: 12px;
            padding: 28px 20px;
            text-align: center;
            margin-bottom: 28px;
            border: 1px solid #e0e0e0;
        }
        .otp-label {
            font-size: 13px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 12px;
        }
        .otp-code {
            font-size: 42px;
            font-weight: 800;
            color: #1565C0;
            letter-spacing: 12px;
            font-family: 'Courier New', 'Monaco', monospace;
            margin-bottom: 12px;
        }
        .otp-expiry {
            font-size: 13px;
            color: #e53935;
            font-weight: 500;
        }
        .warning-box {
            background-color: #fff8e1;
            border-left: 4px solid #ffc107;
            border-radius: 0 8px 8px 0;
            padding: 16px 20px;
            margin-bottom: 24px;
        }
        .warning-box p {
            font-size: 14px;
            color: #6d4c00;
            line-height: 1.6;
        }
        .warning-box strong {
            color: #e65100;
        }
        .ignore-notice {
            font-size: 14px;
            color: #888;
            line-height: 1.6;
            padding-top: 16px;
            border-top: 1px solid #f0f0f0;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 24px 36px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }
        .footer p {
            font-size: 12px;
            color: #aaa;
            line-height: 1.8;
        }
        .footer a {
            color: #2196F3;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <div class="header-logo">🎯 QUIZYFY</div>
                <div class="header-tagline">Platform Ujian & Kuis Online</div>
            </div>

            <!-- Content -->
            <div class="content">
                <div class="greeting">Halo, {{ $userName }}! 👋</div>

                <p class="message">
                    Kami menerima permintaan untuk mereset password akun Quizyfy Anda.
                    Gunakan kode OTP berikut untuk melanjutkan proses reset password:
                </p>

                <!-- OTP Box -->
                <div class="otp-section">
                    <div class="otp-label">Kode Verifikasi OTP</div>
                    <div class="otp-code">{{ $otp }}</div>
                    <div class="otp-expiry">⏱ Kode ini berlaku selama <strong>15 menit</strong></div>
                </div>

                <!-- Warning -->
                <div class="warning-box">
                    <p>
                        🔒 <strong>Jaga kerahasiaan kode ini!</strong><br>
                        Quizyfy tidak pernah meminta kode OTP melalui telepon, chat, atau media apapun.
                        Jangan bagikan kode ini kepada siapapun termasuk pihak yang mengaku dari tim Quizyfy.
                    </p>
                </div>

                <!-- Ignore Notice -->
                <p class="ignore-notice">
                    Jika Anda tidak meminta reset password, abaikan email ini.
                    Akun Anda tetap aman dan tidak akan ada perubahan yang terjadi.
                </p>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>
                    © {{ date('Y') }} Quizyfy. All rights reserved.<br>
                    Email ini dikirim secara otomatis, mohon tidak membalas.<br>
                    <a href="#">Kebijakan Privasi</a> · <a href="#">Bantuan</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
