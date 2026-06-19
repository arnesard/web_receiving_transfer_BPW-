<?php

namespace App\Http\Controllers\so_karantina;

use App\Http\Controllers\Controller;
use App\Models\SoKarantinaScan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SpesialEntriKarantinaController extends Controller
{
    /**
     * Tampilkan form awal: input Team Hitung saja
     * (Team disimpan di session, jadi aman kalau di-refresh)
     */
    public function index()
    {
        return view('so_karantina.2_spesial_entry_karantina', [
            'team' => session('spesial_team'),
        ]);
    }

    /**
     * Setelah submit Team Hitung -> simpan ke session, redirect ke index (GET)
     */
    public function setTeam(Request $request)
    {
        $request->validate([
            'team_hitung' => 'required|string|max:7',  // ← ubah 50 → 7
        ]);
        // ...

        session(['spesial_team' => $request->team_hitung]);

        return redirect()->route('karantina.spesial.index');
    }

    /**
     * Simpan data scan baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'team_hitung' => 'required|string|max:7',  // ← ubah 50 → 7
            'no_doc'      => 'required|string|max:7',  // ← ubah 50 → 7
            'item_code'   => 'required|string|max:100',
            'qty_stock'   => 'required|integer|min:0',
        ]);

        SoKarantinaScan::create([
            'opr'            => $request->team_hitung,
            'NoDoc'          => $request->no_doc,
            'item_code_desc' => $request->item_code,
            'QtyStk'         => $request->qty_stock,
            'txndate'        => Carbon::now()->format('Y-m-d H:i:s'),
            'status'         => 'pending',
        ]);

        return back()->with('success', 'Data berhasil disimpan!');
    }

    /**
     * Reset / ganti team hitung (tombol back)
     */
    public function resetTeam()
    {
        session()->forget('spesial_team');
        return redirect()->route('karantina.spesial.index');
    }
}
