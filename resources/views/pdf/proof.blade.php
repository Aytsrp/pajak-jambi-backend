<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        td { padding: 6px 0; }
        .label { width: 40%; color: #666; }
        .total { font-size: 16px; font-weight: bold; border-top: 1px solid #ccc; padding-top: 10px; }
        .footer { margin-top: 30px; font-size: 10px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Bukti Pembayaran Pajak Daerah</h2>
        <p>Kota Jambi</p>
    </div>

    <table>
        <tr><td class="label">No. Referensi</td><td>: {{ $transaction->transaction_ref }}</td></tr>
        <tr><td class="label">Jenis Pajak</td><td>: {{ $transaction->tax_type->label() }}</td></tr>
        <tr><td class="label">Objek Pajak</td><td>: {{ $objectName }}</td></tr>
        <tr><td class="label">Periode</td><td>: {{ $transaction->bill->tax_period }}</td></tr>
        <tr><td class="label">Metode Pembayaran</td><td>: {{ $transaction->payment->provider ?? '-' }}</td></tr>
        <tr><td class="label">Referensi Gateway</td><td>: {{ $transaction->gateway_ref }}</td></tr>
        <tr><td class="label">Tanggal Bayar</td><td>: {{ $transaction->paid_at?->translatedFormat('d F Y, H:i') }} WIB</td></tr>
    </table>

    <table>
        <tr class="total"><td class="label">Total Dibayar</td><td>: Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td></tr>
    </table>

    <div class="footer">
        Dokumen ini dihasilkan otomatis oleh sistem dan sah tanpa tanda tangan basah.
    </div>
</body>
</html>