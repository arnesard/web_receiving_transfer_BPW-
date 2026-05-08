<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transfer_rak_details', function (Blueprint $table) {

            // index gabungan buat scan duplicate
            $table->index(
                ['transfer_rak_id', 'kode_rak'],
                'idx_transfer_kode_rak'
            );

            // index buat query penerimaan
            $table->index(
                'waktu_diterima',
                'idx_waktu_diterima'
            );

            // index operator
            $table->index(
                'id_karyawan_pengirim',
                'idx_pengirim'
            );

            $table->index(
                'id_penerima',
                'idx_penerima'
            );
        });
    }

    public function down(): void
    {
        Schema::table('transfer_rak_details', function (Blueprint $table) {

            $table->dropIndex('idx_transfer_kode_rak');
            $table->dropIndex('idx_waktu_diterima');
            $table->dropIndex('idx_pengirim');
            $table->dropIndex('idx_penerima');
        });
    }
};
