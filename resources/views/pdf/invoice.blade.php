<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #222;
        }

        .header {
            background: #1A56A0;
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 22px;
            margin-bottom: 4px;
        }

        .header p {
            font-size: 11px;
            opacity: 0.85;
        }

        .badge {
            background: white;
            color: #1A56A0;
            padding: 6px 14px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 13px;
        }

        .body {
            padding: 24px 30px;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #1A56A0;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #1A56A0;
            padding-bottom: 4px;
            margin-bottom: 12px;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .info-row {
            margin-bottom: 7px;
        }

        .info-label {
            color: #666;
            font-size: 10px;
        }

        .info-value {
            font-weight: bold;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        th {
            background: #1A56A0;
            color: white;
            padding: 8px 10px;
            text-align: left;
            font-size: 11px;
        }

        td {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
            font-size: 11px;
        }

        tr:nth-child(even) td {
            background: #f5f8fd;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }

        .status-received {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .status-diagnosa {
            background: #fef3c7;
            color: #92400e;
        }

        .status-perbaikan {
            background: #ffedd5;
            color: #c2410c;
        }

        .status-selesai {
            background: #dcfce7;
            color: #166534;
        }

        .status-diambil {
            background: #f0fdf4;
            color: #15803d;
        }

        .cost-table td {
            padding: 7px 10px;
        }

        .cost-table .total-row td {
            font-weight: bold;
            font-size: 13px;
            background: #EEF4FB;
            color: #1A56A0;
            border-top: 2px solid #1A56A0;
        }

        .footer {
            margin-top: 30px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .sign-box {
            border-top: 1px solid #ccc;
            padding-top: 8px;
            text-align: center;
            margin-top: 50px;
            font-size: 10px;
            color: #555;
        }

        .watermark {
            text-align: center;
            color: #ccc;
            font-size: 10px;
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <div class="header">
        <div>
            <h1>⚙ Service Center</h1>
            <p>Jl. Contoh No. 123, Jakarta Utara | Telp: (021) 1234-5678</p>
            <p>Email: admin@servicecenter.com</p>
        </div>
        <div>
            <div class="badge">INVOICE</div>
            <p style="color:white;margin-top:6px;font-size:10px;text-align:right;">{{ $transaction->order_number }}</p>
        </div>
    </div>

    <div class="body">

        <div class="grid-2">
            <div>
                <div class="section-title">Informasi Pelanggan</div>
                <div class="info-row">
                    <div class="info-label">Nama Pelanggan</div>
                    <div class="info-value">{{ $transaction->customer_name }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">No. Telepon</div>
                    <div class="info-value">{{ $transaction->customer_phone }}</div>
                </div>
                @if ($transaction->customer_email)
                    <div class="info-row">
                        <div class="info-label">Email</div>
                        <div class="info-value">{{ $transaction->customer_email }}</div>
                    </div>
                @endif
            </div>
            <div>
                <div class="section-title">Informasi Order</div>
                <div class="info-row">
                    <div class="info-label">Nomor Order</div>
                    <div class="info-value">{{ $transaction->order_number }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tanggal Masuk</div>
                    <div class="info-value">{{ $transaction->received_date->format('d M Y') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Tanggal Selesai</div>
                    <div class="info-value">
                        {{ $transaction->completed_date ? $transaction->completed_date->format('d M Y') : '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Teknisi</div>
                    <div class="info-value">{{ $transaction->technician?->name ?? '-' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span
                            class="status-badge status-{{ $transaction->status }}">{{ $transaction->status_label }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-title">Detail Servis</div>
        <table>
            <thead>
                <tr>
                    <th style="width:25%">Produk</th>
                    <th style="width:35%">Keluhan</th>
                    <th style="width:40%">Diagnosa & Perbaikan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>{{ $transaction->detail?->product?->name ?? '-' }}</strong><br>
                        <span style="color:#888">{{ $transaction->detail?->product?->category }}</span>
                    </td>
                    <td>{{ $transaction->detail?->complaint ?? '-' }}</td>
                    <td>
                        @if ($transaction->detail?->diagnosis)
                            <strong>Diagnosa:</strong> {{ $transaction->detail->diagnosis }}<br>
                        @endif
                        @if ($transaction->detail?->repair_notes)
                            <strong>Perbaikan:</strong> {{ $transaction->detail->repair_notes }}
                        @endif
                        @if (!$transaction->detail?->diagnosis && !$transaction->detail?->repair_notes)
                            <span style="color:#aaa">Belum ada catatan</span>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="section-title">Rincian Biaya</div>
        <table class="cost-table" style="width:50%;margin-left:auto">
            <tr>
                <td style="color:#555">Estimasi Biaya</td>
                <td style="text-align:right">Rp
                    {{ number_format($transaction->detail?->estimated_cost ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td>Total Biaya</td>
                <td style="text-align:right">Rp
                    {{ number_format($transaction->detail?->actual_cost ?? 0, 0, ',', '.') }}</td>
            </tr>
        </table>

        <div class="footer">
            <div>
                <p style="font-size:10px;color:#666">Terima kasih telah mempercayakan servis Anda kepada kami.</p>
            </div>
            <div>
                <p style="font-size:10px;color:#666;text-align:center">Jakarta, {{ now()->format('d M Y') }}</p>
                <div class="sign-box">Petugas / Admin</div>
            </div>
        </div>

        <div class="watermark">Dokumen ini digenerate secara otomatis oleh sistem Service Center &bull;
            {{ now()->format('d/m/Y H:i') }}</div>
    </div>

</body>

</html>
