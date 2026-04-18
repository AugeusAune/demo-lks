<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Update Status Transaksi</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
        <h2 style="color: #2c3e50; text-align: center; border-bottom: 2px solid #eee; padding-bottom: 10px;">Update Status Transaksi</h2>
        
        <p>Halo <strong>{{ $transaction->customer_name }}</strong>,</p>
        
        <p>Kami ingin menginformasikan bahwa status pesanan service Anda dengan nomor order <strong>{{ $transaction->order_number }}</strong> telah diperbarui.</p>
        
        <div style="background-color: #f8f9fa; padding: 15px; border-left: 4px solid #0d6efd; margin: 20px 0; border-radius: 4px;">
            <p style="margin: 0; font-size: 16px;">
                <strong>Status Saat Ini:</strong> 
                <span style="color: #0d6efd; text-transform: capitalize;">{{ $newStatus }}</span>
            </p>
            @if($notes)
                <p style="margin: 10px 0 0 0;"><strong>Catatan Teknisi:</strong><br/> {{ $notes }}</p>
            @endif
        </div>

        <h3 style="color: #2c3e50; margin-top: 25px;">Rincian Pesanan</h3>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px;">
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #eee; width: 35%;"><strong>Tgl. Masuk</strong></td>
                <td style="padding: 10px; border-bottom: 1px solid #eee;">{{ \Carbon\Carbon::parse($transaction->received_date)->format('d M Y') }}</td>
            </tr>
            @if($transaction->detail && $transaction->detail->product)
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #eee;"><strong>Produk</strong></td>
                <td style="padding: 10px; border-bottom: 1px solid #eee;">{{ $transaction->detail->product->name }}</td>
            </tr>
            @endif
            @if($transaction->detail && $transaction->detail->complaint)
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #eee;"><strong>Keluhan</strong></td>
                <td style="padding: 10px; border-bottom: 1px solid #eee;">{{ $transaction->detail->complaint }}</td>
            </tr>
            @endif
            @if($transaction->technician_id && $transaction->technician)
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #eee;"><strong>Teknisi</strong></td>
                <td style="padding: 10px; border-bottom: 1px solid #eee;">{{ $transaction->technician->name }}</td>
            </tr>
            @endif
        </table>

        @if(in_array($newStatus, ['selesai']))
            <p style="background-color: #d1e7dd; color: #0f5132; padding: 10px; border-radius: 4px; text-align: center;">
                Barang Anda sudah selesai diperbaiki dan siap untuk diambil!
            </p>
        @endif

        <p style="margin-top: 25px;">Terima kasih telah mempercayakan perbaikan barang Anda kepada kami.</p>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; color: #6c757d; font-size: 12px;">
            <p style="margin: 0;">Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>
