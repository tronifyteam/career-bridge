<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pembaruan Status Lowongan Kerja Anda</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; background-color: #f4f7f6; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .header { background-color: #2e6ff2; color: #fff; padding: 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 30px; }
        .content p { margin-bottom: 15px; font-size: 16px; }
        .status-box { padding: 15px; margin-bottom: 25px; border-radius: 4px; font-weight: bold; text-align: center; font-size: 18px; }
        .status-published { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .status-rejected { background-color: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        .details { background-color: #f9f9fc; border-left: 4px solid #2e6ff2; padding: 15px; margin-bottom: 25px; border-radius: 0 4px 4px 0; }
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
            <p>Kami ingin memberitahukan bahwa status lowongan kerja Anda yang berjudul <strong>{{ $job->title }}</strong> telah diperbarui oleh Administrator.</p>
            
            @if($job->status === 'published')
                <div class="status-box status-published">
                    ✅ Status: Disetujui & Dipublikasikan
                </div>
                <p>Lowongan kerja Anda kini sudah tayang dan dapat dilihat oleh para pekerja migran di direktori kami. Selamat merekrut!</p>
            @elseif($job->status === 'rejected')
                <div class="status-box status-rejected">
                    ❌ Status: Ditolak
                </div>
                <div class="details">
                    <p><strong>Alasan Penolakan:</strong></p>
                    <p>{{ $job->rejection_reason ?? 'Tidak ada alasan spesifik yang diberikan. Silakan hubungi admin untuk info lebih lanjut.' }}</p>
                </div>
                <p>Silakan perbaiki informasi lowongan Anda sesuai dengan kebijakan platform dan ajukan kembali.</p>
            @else
                <div class="status-box">
                    ℹ️ Status: {{ ucfirst($job->status) }}
                </div>
            @endif
            
            <p style="text-align: center;">
                <a href="{{ config('app.url') }}/employer/jobs/{{ $job->id }}" class="btn">Lihat Lowongan</a>
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
