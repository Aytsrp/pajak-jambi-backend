<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f4f8; margin: 0; padding: 40px 20px; }
        .container { max-width: 520px; margin: 0 auto; background-color: #ffffff; padding: 40px 32px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); text-align: center; border-top: 6px solid #1E88E5; }
        .logo { width: 110px; height: auto; margin-bottom: 24px; filter: drop-shadow(0px 4px 6px rgba(0,0,0,0.1)); }
        h1 { color: #1B2332; font-size: 26px; margin-top: 0; margin-bottom: 12px; font-weight: 700; }
        p { color: #4b5563; font-size: 15px; line-height: 1.7; margin-bottom: 28px; padding: 0 15px; }
        .otp-box { background-color: #f8fafc; border: 2px dashed #1E88E5; border-radius: 12px; padding: 24px; font-size: 36px; font-weight: 800; letter-spacing: 12px; color: #1E88E5; margin-bottom: 32px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); }
        .warning-box { background-color: #fff1f2; border-left: 4px solid #e11d48; padding: 16px; border-radius: 4px; text-align: left; }
        .warning-box p { color: #be123c; margin: 0; font-size: 14px; padding: 0; font-weight: 500; }
        .footer { font-size: 13px; color: #9ca3af; margin-top: 40px; border-top: 1px solid #e5e7eb; padding-top: 24px; }
        .footer b { color: #1B2332; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Logo Kota Jambi -->
        <img src="{{ $message->embed(public_path('images/jambi-city-seal.png')) }}" alt="Logo Kota Jambi" class="logo">
        
        <h1>Kode Verifikasi</h1>
        <p>Gunakan kode rahasia di bawah ini untuk melanjutkan proses verifikasi di aplikasi <b>Pajak Jambi</b>. Kode ini berlaku selama 5 menit.</p>
        
        <div class="otp-box">{{ $otpCode }}</div>
        
        <div class="warning-box">
            <p><strong>PENTING:</strong> JANGAN BERIKAN KODE INI KEPADA SIAPAPUN, termasuk pihak yang mengatasnamakan Pemerintah Kota Jambi.</p>
        </div>
        
        <div class="footer">
            &copy; {{ date('Y') }} <b>Pajak Jambi</b>.<br>Badan Pengelola Pajak dan Retribusi Daerah Kota Jambi.
        </div>
    </div>
</body>
</html>
