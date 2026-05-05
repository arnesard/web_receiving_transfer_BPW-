<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transfer_raks', function (Blueprint $table) {
            if (!Schema::hasColumn('transfer_raks', 'lokasi_asal')) {
                $table->string('lokasi_asal')->nullable()->after('id_mobil');
            }
            if (!Schema::hasColumn('transfer_raks', 'lokasi_tujuan')) {
                $table->string('lokasi_tujuan')->nullable()->after('lokasi_asal');
            }
            if (!Schema::hasColumn('transfer_raks', 'id_karyawan_penerima')) {
                $table->foreignId('id_karyawan_penerima')->nullable()->constrained('employees')->onDelete('restrict')->after('lokasi_tujuan');
            }
            if (!Schema::hasColumn('transfer_raks', 'waktu_diterima')) {
                $table->timestamp('waktu_diterima')->nullable()->after('id_karyawan_penerima');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transfer_raks', function (Blueprint $table) {
            $table->dropForeign(['id_karyawan_penerima']);
            $table->dropColumn(['lokasi_asal', 'lokasi_tujuan', 'id_karyawan_penerima', 'waktu_diterima']);
        });
    }
};
