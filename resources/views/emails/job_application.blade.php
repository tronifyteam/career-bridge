<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lamaran Baru untuk Lowongan Anda</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background-color: #f4f7f6; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header { background-color: #2e6ff2; color: #fff; padding: 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .content p { margin-bottom: 15px; font-size: 16px; }
        .details { background-color: #f9f9fc; border-left: 4px solid #2e6ff2; padding: 15px; margin-bottom: 25px; border-radius: 0 4px 4px 0; }
        .details strong { color: #555; }
        .btn { display: inline-block; padding: 12px 25px; background-color: #2e6ff2; color: #fff; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 10px; }
        .btn:hover { background-color: #1a56d1; }
        .footer { background-color: #f1f1f1; padding: 15px; text-align: center; font-size: 12px; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>2ne5 Migrant Work</h1>
        </div>
        <div class="content">
            <p>Halo,</p>
            <p>Ada pekerja yang baru saja melamar ke lowongan kerja Anda: <strong>{{ $job->title }}</strong>.</p>
            
            <div class="details">
                <p><strong>Nama Pelamar:</strong> {{ $application->applicant_name }}</p>
                <p><strong>Pesan:</strong> {{ $application->cover_letter ?? 'Tidak ada pesan tambahan' }}</p>
                <p><strong>Waktu Melamar:</strong> {{ $application->created_at->format('d M Y, H:i') }}</p>
            </div>

            <p>Segera tinjau profil dan dokumen pelamar ini untuk memutuskan apakah Anda ingin melanjutkan ke tahap wawancara.</p>
            
            <p style="text-align: center;">
                <a href="{{ config('app.url') }}/employer/applicants/{{ $application->id }}" class="btn">Tinjau Lamaran</a>
            </p>
            
            <p>Terima kasih telah menggunakan 2ne5 Migrant Work Platform.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} 2ne5 Migrant Work. Hak cipta dilindungi undang-undang.<br>
            Email ini dibuat secara otomatis, mohon tidak membalas ke alamat ini.
        </div>
    </div>
</body>
</html>
