<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\StatusHistory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Users
        $admin = User::create([
            'name'      => 'Admin Utama',
            'email'     => 'admin@service.com',
            'password'  => Hash::make('password'),
            'role'      => 'admin',
            'phone'     => '081234567890',
            'is_active' => true,
        ]);

        $tech1 = User::create([
            'name'      => 'Budi Teknisi',
            'email'     => 'teknisi@service.com',
            'password'  => Hash::make('password'),
            'role'      => 'technician',
            'phone'     => '081234567891',
            'is_active' => true,
        ]);

        $tech2 = User::create([
            'name'      => 'Sari Teknisi',
            'email'     => 'sari@service.com',
            'password'  => Hash::make('password'),
            'role'      => 'technician',
            'phone'     => '081234567892',
            'is_active' => true,
        ]);

        User::create([
            'name'      => 'Kasir Utama',
            'email'     => 'kasir@service.com',
            'password'  => Hash::make('password'),
            'role'      => 'cashier',
            'phone'     => '081234567893',
            'is_active' => true,
        ]);

        // Products
        $products = [
            ['name' => 'Laptop Acer',    'category' => 'Laptop',    'description' => 'Semua seri laptop Acer'],
            ['name' => 'Laptop ASUS',    'category' => 'Laptop',    'description' => 'Semua seri laptop ASUS'],
            ['name' => 'Laptop HP',      'category' => 'Laptop',    'description' => 'Semua seri laptop HP'],
            ['name' => 'Laptop Lenovo',  'category' => 'Laptop',    'description' => 'Semua seri laptop Lenovo'],
            ['name' => 'iPhone',         'category' => 'Handphone', 'description' => 'Semua seri iPhone'],
            ['name' => 'Samsung Galaxy', 'category' => 'Handphone', 'description' => 'Semua seri Samsung Galaxy'],
            ['name' => 'Xiaomi',         'category' => 'Handphone', 'description' => 'Semua seri Xiaomi'],
            ['name' => 'Printer Canon',  'category' => 'Printer',   'description' => 'Semua seri printer Canon'],
            ['name' => 'Printer Epson',  'category' => 'Printer',   'description' => 'Semua seri printer Epson'],
            ['name' => 'iPad / Tablet',  'category' => 'Tablet',    'description' => 'iPad dan tablet lainnya'],
        ];

        foreach ($products as $p) {
            Product::create($p);
        }

        // Sample transactions
        $sampleTransactions = [
            [
                'header' => [
                    'customer_name'  => 'Andi Saputra',
                    'customer_phone' => '08111111111',
                    'customer_email' => 'andi@email.com',
                    'technician_id'  => $tech1->id,
                    'created_by'     => $admin->id,
                    'status'         => 'perbaikan',
                    'received_date'  => now()->subDays(3)->toDateString(),
                ],
                'detail' => [
                    'product_id'     => 1,
                    'complaint'      => 'Layar tidak menyala sama sekali',
                    'diagnosis'      => 'Backlight rusak, perlu penggantian',
                    'estimated_cost' => 350000,
                    'actual_cost'    => 0,
                ],
                'histories' => ['received', 'diagnosa', 'perbaikan'],
            ],
            [
                'header' => [
                    'customer_name'  => 'Dewi Lestari',
                    'customer_phone' => '08222222222',
                    'customer_email' => 'dewi@email.com',
                    'technician_id'  => $tech2->id,
                    'created_by'     => $admin->id,
                    'status'         => 'selesai',
                    'received_date'  => now()->subDays(7)->toDateString(),
                    'completed_date' => now()->subDays(2)->toDateString(),
                ],
                'detail' => [
                    'product_id'     => 6,
                    'complaint'      => 'Baterai cepat habis dan HP sering mati sendiri',
                    'diagnosis'      => 'Baterai rusak, butuh penggantian',
                    'repair_notes'   => 'Baterai berhasil diganti dengan yang baru',
                    'estimated_cost' => 250000,
                    'actual_cost'    => 230000,
                ],
                'histories' => ['received', 'diagnosa', 'perbaikan', 'selesai'],
            ],
            [
                'header' => [
                    'customer_name'  => 'Riko Firmansyah',
                    'customer_phone' => '08333333333',
                    'technician_id'  => $tech1->id,
                    'created_by'     => $admin->id,
                    'status'         => 'received',
                    'received_date'  => now()->toDateString(),
                ],
                'detail' => [
                    'product_id'     => 8,
                    'complaint'      => 'Printer tidak mau print, kertas sering macet',
                    'estimated_cost' => 0,
                ],
                'histories' => ['received'],
            ],
        ];

        foreach ($sampleTransactions as $sample) {
            $trx = Transaction::create(array_merge(
                $sample['header'],
                ['order_number' => Transaction::generateOrderNumber()]
            ));

            TransactionDetail::create(array_merge(
                $sample['detail'],
                ['transaction_id' => $trx->id]
            ));

            foreach ($sample['histories'] as $status) {
                StatusHistory::create([
                    'transaction_id' => $trx->id,
                    'status'         => $status,
                    'notes'          => "Status diubah ke: $status",
                    'changed_by'     => $admin->id,
                ]);
            }
        }
    }
}
