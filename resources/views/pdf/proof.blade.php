<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        td { padding: 6px 0; vertical-align: top; }
        .label { width: 40%; color: #666; }
        .section-title { font-weight: bold; margin-top: 18px; margin-bottom: 4px; border-bottom: 1px solid #ddd; padding-bottom: 4px; }
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
        <tr><td class="label">Tanggal Bayar</td><td>: {{ $transaction->paid_at?->translatedFormat('d F Y, H:i') }} WIB</td></tr>
    </table>

    <div class="section-title">Objek Pajak</div>
    <table>
        <tr><td class="label">Nomor Objek Pajak</td><td>: {{ $referenceNumber }}</td></tr>
        <tr><td class="label">Alamat/Nama Objek</td><td>: {{ $objectName }}</td></tr>
        <tr><td class="label">Atas Nama (terdaftar)</td><td>: {{ $registeredOwnerName ?? '-' }}</td></tr>
        <tr><td class="label">Periode/Tahun Pajak</td><td>: {{ $transaction->bill->tax_period }}</td></tr>
    </table>

    <div class="section-title">Pembayaran</div>
    <table>
        <tr><td class="label">Dibayar Oleh</td><td>: {{ $transaction->user->full_name }}</td></tr>
        <tr><td class="label">Metode Pembayaran</td><td>: {{ $paymentMethodLabel }}</td></tr>
        <tr><td class="label">Referensi Gateway</td><td>: {{ $transaction->gateway_ref }}</td></tr>
    </table>

    <table>
        <tr><td class="label">Pokok Pajak</td><td>: Rp {{ number_format($transaction->bill->amount_due, 0, ',', '.') }}</td></tr>
        <tr><td class="label">Denda</td><td>: Rp {{ number_format($transaction->bill->penalty_amount, 0, ',', '.') }}</td></tr>
        <tr class="total"><td class="label">Total Dibayar</td><td>: Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td></tr>
    </table>

    <div class="footer">
        Dokumen ini dihasilkan otomatis oleh sistem dan sah tanpa tanda tangan basah.
    </div>
</body>
</html>