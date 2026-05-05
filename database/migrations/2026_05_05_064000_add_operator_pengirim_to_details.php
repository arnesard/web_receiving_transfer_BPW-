<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transfer_rak_details', function (Blueprint $table) {
            if (!Schema::hasColumn('transfer_rak_details', 'id_karyawan_pengirim')) {
                $table->unsignedBigInteger('id_karyawan_pengirim')->nullable()->after('transfer_rak_id');
                $table->foreign('id_karyawan_pengirim')->references('id')->on('employees');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transfer_rak_details', function (Blueprint $table) {
            $table->dropForeign(['id_karyawan_pengirim']);
            $table->dropColumn('id_karyawan_pengirim');
        });
    }
};
