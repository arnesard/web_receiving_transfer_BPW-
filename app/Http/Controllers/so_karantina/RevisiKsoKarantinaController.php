<?php

namespace App\Http\Controllers\so_karantina;

use App\Http\Controllers\Controller;
use App\Models\SoKarantinaScan;
use Illuminate\Http\Request;

class RevisiKsoKarantinaController extends Controller
{
    /**
     * Tampilkan form awal: input Team Hitung + Kode Dokumen
     * (disimpan di session, jadi aman kalau di-refresh)
     */
    public function index()
    {
        return view('so_karantina.3_revisi_kso_karantina', [
            'team' => session('revisi_team'),
            'kodeDokumen' => session('revisi_kode_dokumen'),
        ]);
    }

    /**
     * Setelah submit Team Hitung + Kode Dokumen -> simpan ke session, redirect ke index (GET)
     */
    public function setFilter(Request $request)
    {
        $request->validate([
            'team_hitung'  => 'required|string|max:50',
            'kode_dokumen' => 'required|string|max:50',
        ]);

        session([
            'revisi_team' => $request->team_hitung,
            'revisi_kode_dokumen' => $request->kode_dokumen,
        ]);

        return redirect()->route('karantina.revisi.index');
    }

    /**
     * Reset / ganti team hitung (tombol back)
     */
    public function resetTeam()
    {
        session()->forget(['revisi_team', 'revisi_kode_dokumen']);
        return redirect()->route('karantina.revisi.index');
    }


    /**
     * Simpan hasil revisi (update data lama)
     */
    public function update(Request $request)
    {
        $request->validate([
            'no_doc'    => 'required|string|max:50',
            'item_code' => 'required|string|max:100',
            'qty_stock' => 'required|integer|min:0',
        ]);

        $data = SoKarantinaScan::where('NoDoc', $request->no_doc)->firstOrFail();

        $data->update([
            'item_code_desc' => $request->item_code,
            'QtyStk'         => $request->qty_stock,
        ]);

        return back()->with('success', 'Data berhasil direvisi!');
    }
}
