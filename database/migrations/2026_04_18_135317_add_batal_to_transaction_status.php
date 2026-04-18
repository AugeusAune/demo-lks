<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            DB::statement("ALTER TABLE transactions DROP CONSTRAINT IF EXISTS transactions_status_check");
            // Menambahkan 'batal'
            DB::statement("ALTER TABLE transactions ADD CONSTRAINT transactions_status_check CHECK (status::text = ANY (ARRAY['received'::character varying, 'diagnosa'::character varying, 'perbaikan'::character varying, 'selesai'::character varying, 'diambil'::character varying, 'batal'::character varying]::text[]))");
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            DB::statement("ALTER TABLE transactions DROP CONSTRAINT IF EXISTS transactions_status_check");
            DB::statement("ALTER TABLE transactions ADD CONSTRAINT transactions_status_check CHECK (status::text = ANY (ARRAY['received'::character varying, 'diagnosa'::character varying, 'perbaikan'::character varying, 'selesai'::character varying, 'diambil'::character varying]::text[]))");
        });
    }
};
