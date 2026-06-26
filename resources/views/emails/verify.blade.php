<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verifikasi Email Anda - 2ne5</title>
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
        .code-container {
            text-align: center;
            margin: 32px 0;
        }
        .code-box {
            background-color: #f8fafc;
            color: #1a3bb5;
            border: 2px dashed #1a3bb5;
            letter-spacing: 6px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 32px;
            font-weight: 800;
            padding: 16px 30px;
            border-radius: 10px;
            display: inline-block;
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
        <p>Terima kasih telah mendaftar di aplikasi 2ne5. Silakan masukkan kode verifikasi 6 digit di bawah ini di aplikasi mobile untuk memverifikasi alamat email Anda:</p>
        
        <div class="code-container">
            <div class="code-box">{{ $code }}</div>
        </div>
        
        <p>Kode ini berlaku selama 15 menit. Jika Anda tidak melakukan pendaftaran ini, silakan abaikan email ini dengan aman.</p>
        
        <div class="footer">
            &copy; {{ date('Y') }} 2ne5. All rights reserved.<br>
            Email ini dikirim secara otomatis. Jangan membalas email ini.
        </div>
    </div>
</body>
</html>
