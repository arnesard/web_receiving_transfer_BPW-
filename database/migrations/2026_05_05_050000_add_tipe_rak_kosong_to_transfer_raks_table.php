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
            if (!Schema::hasColumn('transfer_raks', 'tipe')) {
                $table->string('tipe', 20)->default('transfer')->after('id');
            }
            if (!Schema::hasColumn('transfer_raks', 'jumlah_rak_kosong')) {
                $table->integer('jumlah_rak_kosong')->default(0)->after('total_rak');
            }
            if (!Schema::hasColumn('transfer_raks', 'jumlah_palet_kosong')) {
                $table->integer('jumlah_palet_kosong')->default(0)->after('jumlah_rak_kosong');
            }
        });

        // Index juga dicek biar aman
        Schema::table('transfer_raks', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = collect($sm->listTableIndexes('transfer_raks'))->keys()->toArray();
            if (!in_array('transfer_raks_tipe_index', $indexes)) {
                $table->index('tipe');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transfer_raks', function (Blueprint $table) {
            $table->dropIndex(['tipe']);
            $table->dropColumn(['tipe', 'jumlah_rak_kosong', 'jumlah_palet_kosong']);
        });
    }
};
