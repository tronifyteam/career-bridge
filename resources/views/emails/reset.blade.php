<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Atur Ulang Kata Sandi - 2ne5</title>
    <style>
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        .logo-wrapper {
            text-align: center;
            margin-bottom: 30px;
        }
        h2 {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 20px;
        }
        p {
            font-size: 15px;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 24px;
        }
        .btn-container {
            text-align: center;
            margin: 32px 0;
        }
        .btn {
            background-color: #1a3bb5;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 30px;
            font-size: 15px;
            font-weight: 600;
            border-radius: 8px;
            display: inline-block;
            box-shadow: 0 4px 10px rgba(26, 59, 181, 0.2);
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: #0f2680;
        }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
            font-size: 12px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-wrapper">
            <table align="center" border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                <tr>
                    <td style="vertical-align: middle; padding-right: 10px;">
                        <div style="background-color: #1a3bb5; width: 36px; height: 36px; border-radius: 8px; display: inline-block; text-align: center; line-height: 36px;">
                            <span style="color: #ffffff; font-family: 'Segoe UI', system-ui, sans-serif; font-size: 20px; font-weight: 800;">2</span>
                        </div>
                    </td>
                    <td style="vertical-align: middle;">
                        <span style="font-family: 'Segoe UI', system-ui, sans-serif; font-size: 26px; font-weight: 800; color: #1a3bb5; letter-spacing: -0.5px;">2ne5</span>
                    </td>
                </tr>
            </table>
        </div>
        <h2>Halo, {{ $name }}!</h2>
        <p>Anda menerima email ini karena kami menerima permintaan untuk mengatur ulang kata sandi akun Anda di aplikasi 2ne5.</p>
        <p>Silakan klik tombol di bawah ini untuk masuk ke halaman pengaturan ulang kata sandi Anda. Tautan ini hanya berlaku selama 60 menit.</p>
        
        <div class="btn-container">
            <a href="{{ $resetUrl }}" class="btn" target="_blank">Atur Ulang Kata Sandi</a>
        </div>
        
        <p>Jika Anda tidak meminta pengaturan ulang kata sandi, Anda dapat mengabaikan email ini dengan aman.</p>
        
        <div class="footer">
            &copy; {{ date('Y') }} 2ne5. All rights reserved.<br>
            Jika Anda mengalami kendala mengeklik tombol di atas, salin dan tempel URL berikut ke peramban Anda:<br>
            <span style="word-break: break-all; color: #1a3bb5;">{{ $resetUrl }}</span>
        </div>
    </div>
</body>
</html>
