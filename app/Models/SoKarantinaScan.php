<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SoKarantinaScan extends Model
{
    protected $connection = 'so_karantina';
    protected $table = 'so_karantina_scan';

    public $timestamps = false;

    protected $fillable = [
        'opr',
        'NoDoc',
        'item_code_desc',
        'QtyStk',
        'txndate',
        'status',
        'opr_v',
        'scantime_v',
        'ket_reject',
    ];

    /**
     * Ambil kode dokumen dari prefix NoDoc (huruf di awal sebelum angka)
     * Contoh: G2B2401 -> G2B
     */
    public static function extractKodeDokumen($noDoc)
    {
        preg_match('/^[A-Za-z]+/', $noDoc, $matches);
        return $matches[0] ?? '';
    }
}
