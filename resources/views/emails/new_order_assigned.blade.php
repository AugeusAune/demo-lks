<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tugas Masuk: Pesanan Servis Baru</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f7f6; margin: 0; padding: 20px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; background-color: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
        <!-- Header -->
        <tr>
            <td style="background-color: #0d6efd; padding: 20px; text-align: center;">
                <h2 style="color: #fff; margin: 0; font-size: 20px;">Tugas Baru Ditugaskan</h2>
            </td>
        </tr>

        <!-- Body -->
        <tr>
            <td style="padding: 30px;">
                <p>Halo <strong>{{ $transaction->technician->name }}</strong>,</p>
                <p>Ada pesanan servis baru yang telah didelegasikan kepada Anda dengan Nomor Order <strong><span style="color: #0d6efd; font-weight: bold;">{{ $transaction->order_number }}</span></strong>.</p>
                
                <h3 style="color: #2c3e50; margin-top: 25px; border-bottom: 2px solid #eee; padding-bottom: 5px;">Rincian Pesanan</h3>
                <table width="100%" style="border-collapse: collapse; margin-bottom: 20px; font-size: 14px;">
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; width: 35%;">Pelanggan</td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">{{ $transaction->customer_name }}</td>
                    </tr>
                    @if($transaction->detail && $transaction->detail->product)
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Produk</td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">{{ $transaction->detail->product->name }}</td>
                    </tr>
                    @endif
                    @if($transaction->detail && $transaction->detail->complaint)
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Keluhan</td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;"><span style="background-color: #f8f9fa; padding: 5px 10px; border-radius: 4px; display: inline-block;">{{ $transaction->detail->complaint }}</span></td>
                    </tr>
                    @endif
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold;">Tgl. Masuk</td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">{{ \Carbon\Carbon::parse($transaction->received_date)->format('d M Y') }}</td>
                    </tr>
                </table>

                <p style="margin-top: 20px;">Silakan cek selengkapnya di aplikasi sistem Service Center dan segera lakukan pengecekan pada barang pelanggan.</p>
                
                <p style="margin-top: 30px;">Semangat bertugas!</p>
            </td>
        </tr>

        <!-- Footer -->
        <tr>
            <td style="background-color: #f8f9fa; padding: 15px; text-align: center; border-top: 1px solid #eee;">
                <p style="margin: 0; color: #6c757d; font-size: 12px;">Email ini dikirim secara otomatis oleh sistem. Mohon tidak membalas email ini.</p>
            </td>
        </tr>
    </table>
</body>
</html>
