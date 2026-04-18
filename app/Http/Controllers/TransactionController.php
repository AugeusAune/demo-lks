<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\StatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with(['technician:id,name', 'detail.product:id,name', 'creator:id,name']);

        $user = auth()->user();

        // Teknisi hanya lihat order miliknya
        if ($user->role === 'technician') {
            $query->where('technician_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('order_number', 'ilike', "%{$request->search}%")
                    ->orWhere('customer_name', 'ilike', "%{$request->search}%")
                    ->orWhere('customer_phone', 'ilike', "%{$request->search}%");
            });
        }
        if ($request->filled('date_from')) {
            $query->whereDate('received_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('received_date', '<=', $request->date_to);
        }
        if ($request->filled('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json(['data' => $transactions]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name'         => 'required|string|max:100',
            'customer_phone'        => 'required|string|max:20',
            'customer_email'        => 'nullable|email',
            'technician_id'         => 'nullable|exists:users,id',
            'received_date'         => 'required|date',
            'detail.product_id'     => 'required|exists:products,id',
            'detail.complaint'      => 'required|string',
            'detail.estimated_cost' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'customer_name'  => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_email' => $request->customer_email,
                'technician_id'  => $request->technician_id,
                'created_by'     => auth()->id(),
                'status'         => 'received',
                'received_date'  => $request->received_date,
            ]);

            $detail = $request->input('detail');
            TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'product_id'     => $detail['product_id'],
                'complaint'      => $detail['complaint'],
                'estimated_cost' => $detail['estimated_cost'] ?? 0,
            ]);

            // Catat riwayat status
            StatusHistory::create([
                'transaction_id' => $transaction->id,
                'status'         => 'received',
                'notes'          => 'Order baru diterima',
                'changed_by'     => auth()->id(),
            ]);

            DB::commit();

            $transaction->load(['technician:id,name,email', 'detail.product', 'creator:id,name']);

            // Notifikasi email ke teknisi
            if ($transaction->technician_id && $transaction->technician?->email) {
                try {
                    Mail::send('emails.new_order_assigned', [
                        'transaction' => $transaction,
                    ], function ($message) use ($transaction) {
                        $message->to($transaction->technician->email, $transaction->technician->name)
                            ->subject("Tugas Masuk Baru - Order {$transaction->order_number}");
                    });
                } catch (\Exception $e) {
                    \Log::warning('Gagal kirim email notifikasi teknisi: ' . $e->getMessage());
                }
            }

            return response()->json([

                'message' => 'Transaksi berhasil dibuat',
                'data'    => $transaction,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal membuat transaksi: ' . $e->getMessage()], 500);
        }
    }

    public function show(Transaction $transaction)
    {
        $user = auth()->user();

        // Teknisi hanya boleh lihat miliknya
        if ($user->role === 'technician' && $transaction->technician_id !== $user->id) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $transaction->load([
            'technician:id,name,phone',
            'detail.product',
            'statusHistories.changedBy:id,name,role',
            'creator:id,name',
        ]);

        return response()->json(['data' => $transaction]);
    }

    public function update(Request $request, Transaction $transaction)
    {
        $request->validate([
            'customer_name'         => 'required|string|max:100',
            'customer_phone'        => 'required|string|max:20',
            'customer_email'        => 'nullable|email',
            'technician_id'         => 'nullable|exists:users,id',
            'received_date'         => 'required|date',
            'detail.product_id'     => 'required|exists:products,id',
            'detail.complaint'      => 'required|string',
            'detail.diagnosis'      => 'nullable|string',
            'detail.repair_notes'   => 'nullable|string',
            'detail.estimated_cost' => 'nullable|numeric|min:0',
            'detail.actual_cost'    => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $oldTechnicianId = $transaction->technician_id;

            $transaction->update([
                'customer_name'  => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_email' => $request->customer_email,
                'technician_id'  => $request->technician_id,
                'received_date'  => $request->received_date,
            ]);

            $detail = $request->input('detail');
            $transaction->detail()->updateOrCreate(
                ['transaction_id' => $transaction->id],
                [
                    'product_id'     => $detail['product_id'],
                    'complaint'      => $detail['complaint'],
                    'diagnosis'      => $detail['diagnosis'] ?? null,
                    'repair_notes'   => $detail['repair_notes'] ?? null,
                    'estimated_cost' => $detail['estimated_cost'] ?? 0,
                    'actual_cost'    => $detail['actual_cost'] ?? 0,
                ]
            );

            DB::commit();

            $transaction->load(['technician:id,name,email', 'detail.product', 'creator:id,name']);

            // Notifikasi email ke teknisi jika dikasih tugas baru
            if ($request->technician_id && $request->technician_id != $oldTechnicianId && $transaction->technician?->email) {
                try {
                    Mail::send('emails.new_order_assigned', [
                        'transaction' => $transaction,
                    ], function ($message) use ($transaction) {
                        $message->to($transaction->technician->email, $transaction->technician->name)
                            ->subject("Tugas Masuk Baru - Order {$transaction->order_number}");
                    });
                } catch (\Exception $e) {
                    \Log::warning('Gagal kirim email delegasi teknisi: ' . $e->getMessage());
                }
            }

            return response()->json(['message' => 'Transaksi berhasil diupdate', 'data' => $transaction]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mengupdate transaksi: ' . $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, Transaction $transaction)
    {
        $request->validate([
            'status'      => 'required|in:received,diagnosa,perbaikan,selesai,diambil,batal',
            'notes'       => 'nullable|string',
            'actual_cost' => 'nullable|numeric|min:0',
        ]);

        $user = auth()->user();

        // Teknisi hanya update miliknya
        if ($user->role === 'technician' && $transaction->technician_id !== $user->id) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        if ($user->role === 'technician' && $request->status === 'diambil') {
            return response()->json(['message' => 'Teknisi tidak diizinkan menandai barang sebagai diambil!'], 403);
        }

        if ($user->role === 'technician' && $request->status === 'batal') {
            return response()->json(['message' => 'Teknisi tidak diizinkan menandai barang sebagai batal!'], 403);
        }

        if ($user->role === 'technician' && $transaction->status === 'batal') {
            return response()->json(['message' => 'Status sudah batal, tidak dapat diubah oleh teknisi.'], 400);
        }

        if ($transaction->status === 'diambil') {
            return response()->json(['message' => 'Status sudah tidak dapat diubah karena barang sudah diambil pelanggan.'], 400);
        }

        DB::beginTransaction();
        try {
            $oldStatus = $transaction->status;
            $newStatus = $request->status;

            $updateData = ['status' => $newStatus];

            if ($newStatus === 'selesai' && !$transaction->completed_date) {
                // Pertama kali selesai langsung catat waktu
                $updateData['completed_date'] = now();
            } elseif (!in_array($newStatus, ['selesai', 'diambil'])) {
                // Kalau dibatalkan/kembali ke proses, hapus waktu selesainya
                $updateData['completed_date'] = null;
            }

            $transaction->update($updateData);

            // Sync notes ke dalam column diagnosa / perbaikan di details
            if ($request->filled('notes')) {
                if ($newStatus === 'diagnosa') {
                    $transaction->detail()->update(['diagnosis' => $request->notes]);
                } elseif ($newStatus === 'perbaikan') {
                    $transaction->detail()->update(['repair_notes' => $request->notes]);
                }
            }

            // Sync actual cost ketika status disetel 'selesai' atau 'perbaikan' jika ada input
            if ($request->filled('actual_cost') && in_array($newStatus, ['selesai', 'perbaikan'])) {
                $transaction->detail()->update(['actual_cost' => $request->actual_cost]);
            }

            StatusHistory::create([
                'transaction_id' => $transaction->id,
                'status'         => $newStatus,
                'notes'          => $request->notes,
                'changed_by'     => $user->id,
            ]);

            // Kirim email notifikasi jika ada email pelanggan
            if ($transaction->customer_email) {
                try {
                    Mail::send('emails.status_update', [
                        'transaction' => $transaction,
                        'newStatus'   => $newStatus,
                        'notes'       => $request->notes,
                    ], function ($message) use ($transaction) {
                        $message->to($transaction->customer_email, $transaction->customer_name)
                            ->subject("Update Status Order {$transaction->order_number}");
                    });
                } catch (\Exception $e) {
                    // Jangan gagalkan request hanya karena email gagal
                    \Log::warning('Gagal kirim email notifikasi: ' . $e->getMessage());
                }
            }

            DB::commit();

            return response()->json(['message' => 'Status berhasil diupdate', 'data' => $transaction]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal update status: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Transaction $transaction)
    {
        $transaction->delete();
        return response()->json(['message' => 'Transaksi berhasil dihapus']);
    }

    public function tracking($orderNumber)
    {
        $transaction = Transaction::with([
            'technician:id,name',
            'detail.product:id,name,category',
            'statusHistories.changedBy:id,name',
        ])->where('order_number', $orderNumber)->first();

        if (!$transaction) {
            return response()->json(['message' => 'Nomor order tidak ditemukan'], 404);
        }

        return response()->json([

            'data'    => [
                'order_number'    => $transaction->order_number,
                'customer_name'   => $transaction->customer_name,
                'received_date'   => $transaction->received_date,
                'completed_date'  => $transaction->completed_date,
                'status'          => $transaction->status,
                'status_label'    => $transaction->status_label,
                'technician_name' => $transaction->technician?->name,
                'product'         => $transaction->detail?->product,
                'complaint'       => $transaction->detail?->complaint,
                'status_histories' => $transaction->statusHistories->map(fn($h) => [
                    'status'       => $h->status,
                    'status_label' => $h->status_label,
                    'notes'        => $h->notes,
                    'changed_by'   => $h->changedBy?->name,
                    'changed_at'   => $h->created_at,
                ]),
            ],
        ]);
    }

    public function invoice(Transaction $transaction)
    {
        $transaction->load(['technician:id,name,phone', 'detail.product', 'creator:id,name']);

        $pdf = Pdf::loadView('pdf.invoice', ['transaction' => $transaction]);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download("invoice-{$transaction->order_number}.pdf");
    }

    public function dashboard()
    {
        $today = today();
        $user  = auth()->user();

        if ($user->role === 'technician') {
            return response()->json([
                'data' => [
                    'total_orders'  => Transaction::where('technician_id', $user->id)->count(),
                    'active_orders' => Transaction::where('technician_id', $user->id)
                                        ->whereNotIn('status', ['selesai', 'diambil'])->count(),
                    'done_orders'   => Transaction::where('technician_id', $user->id)
                                        ->whereIn('status', ['selesai', 'diambil'])->count(),
                    'today_orders'  => Transaction::where('technician_id', $user->id)
                                        ->whereDate('received_date', $today)->count(),
                    'recent'        => Transaction::where('technician_id', $user->id)
                                        ->with(['detail.product:id,name'])
                                        ->orderBy('created_at', 'desc')
                                        ->limit(5)
                                        ->get(),
                ],
            ]);
        }

        // Admin / Cashier
        return response()->json([
            'data' => [
                'today_orders'     => Transaction::whereDate('received_date', $today)->count(),
                'active_orders'    => Transaction::whereNotIn('status', ['selesai', 'diambil'])->count(),
                'done_this_month'  => Transaction::whereIn('status', ['selesai', 'diambil'])
                                        ->whereMonth('created_at', $today->month)
                                        ->whereYear('created_at', $today->year)
                                        ->count(),
                'total_revenue'    => TransactionDetail::whereHas('transaction', function ($q) use ($today) {
                                        $q->whereIn('status', ['selesai', 'diambil'])
                                          ->whereMonth('created_at', $today->month)
                                          ->whereYear('created_at', $today->year);
                                    })->sum('actual_cost'),
                'technician_count' => \App\Models\User::where('role', 'technician')
                                        ->where('is_active', true)->count(),
                'status_breakdown' => Transaction::selectRaw('status, count(*) as total')
                                        ->groupBy('status')
                                        ->pluck('total', 'status'),
                'recent'           => Transaction::with(['technician:id,name', 'detail.product:id,name'])
                                        ->orderBy('created_at', 'desc')
                                        ->limit(8)
                                        ->get(),
            ],
        ]);
    }
}
