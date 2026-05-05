<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Kolom penerimaan per-rak di detail
        Schema::table('transfer_rak_details', function (Blueprint $table) {
            if (!Schema::hasColumn('transfer_rak_details', 'lokasi_diterima')) {
                $table->string('lokasi_diterima')->nullable()->after('waktu_scan');
            }
            if (!Schema::hasColumn('transfer_rak_details', 'id_penerima')) {
                $table->unsignedBigInteger('id_penerima')->nullable()->after('lokasi_diterima');
            }
            if (!Schema::hasColumn('transfer_rak_details', 'waktu_diterima')) {
                $table->timestamp('waktu_diterima')->nullable()->after('id_penerima');
            }
        });

        // Update status enum: tambah 'sebagian' dan 'diterima'
        // Karena MySQL enum susah diubah, kita ganti ke string
        if (Schema::hasColumn('transfer_raks', 'status')) {
            DB::statement("ALTER TABLE transfer_raks MODIFY COLUMN status VARCHAR(20) DEFAULT 'proses'");
        }
    }

    public function down(): void
    {
        Schema::table('transfer_rak_details', function (Blueprint $table) {
            $table->dropColumn(['lokasi_diterima', 'id_penerima', 'waktu_diterima']);
        });
    }
};
