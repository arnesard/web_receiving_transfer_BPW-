<?php

namespace App\Http\Controllers\so_karantina;

use App\Http\Controllers\Controller;
use App\Models\SoKarantinaScan;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ValidasiKsoKarantinaController extends Controller
{
    /**
     * Tampilkan halaman sesuai step yang sudah dicapai (dibaca dari session):
     * - Belum ada team   -> Step 1: form Team Hitung
     * - Ada team, belum ada No.Doc -> Step 2: form No.Doc
     * - Ada team + No.Doc -> Step 3: tampil detail data + tombol approve/reject
     */
    public function index()
    {
        $team = session('validasi_team');
        $noDoc = session('validasi_no_doc');
        $item = null;
        $notFound = false;

        if ($team && $noDoc) {
            $item = SoKarantinaScan::where('NoDoc', $noDoc)
                ->where('status', 'pending')
                ->orderByDesc('id')
                ->first();

            if (!$item) {
                $notFound = true;
            }
        }

        return view('so_karantina.4_validasi_kso_karantina', [
            'team' => $team,
            'noDoc' => $noDoc,
            'item' => $item,
            'notFound' => $notFound,
        ]);
    }

    /**
     * Step 1 -> Step 2: simpan Team Hitung ke session
     */
    public function setTeam(Request $request)
    {
        $request->validate([
            'team_hitung' => 'required|string|max:50',
        ]);

        session(['validasi_team' => $request->team_hitung]);
        session()->forget('validasi_no_doc');

        return redirect()->route('karantina.validasi.index');
    }

    /**
     * Step 2 -> Step 3: simpan No.Doc ke session
     */
    public function setNoDoc(Request $request)
    {
        $request->validate([
            'no_doc' => 'required|string|max:50',
        ]);

        session(['validasi_no_doc' => $request->no_doc]);

        return redirect()->route('karantina.validasi.index');
    }

    /**
     * Tombol "<-" di Step 2: reset Team Hitung, balik ke Step 1
     */
    public function resetTeam()
    {
        session()->forget(['validasi_team', 'validasi_no_doc']);
        return redirect()->route('karantina.validasi.index');
    }

    /**
     * Tombol "<-" di Step 3: reset No.Doc saja, balik ke Step 2 (Team Hitung tetap)
     */
    public function resetNoDoc()
    {
        session()->forget('validasi_no_doc');
        return redirect()->route('karantina.validasi.index');
    }

    /**
     * Approve data yang sedang ditampilkan
     */
    public function approve(Request $request, $id)
    {
        $data = SoKarantinaScan::findOrFail($id);
        $data->update([
            'status'      => 'approved',
            'opr_v' => session('validasi_team'),
            'scantime_v'  => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        session()->forget('validasi_no_doc');

        return redirect()->route('karantina.validasi.index')->with('success', 'Data berhasil di-approve!');
    }

    /**
     * Reject data yang sedang ditampilkan + simpan keterangan
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'ket_reject' => 'required|string|max:150',
        ]);

        $data = SoKarantinaScan::findOrFail($id);
        $data->update([
            'status'      => 'rejected',
            'ket_reject'  => $request->ket_reject,
            'opr_v' => session('validasi_team'),
            'scantime_v'  => Carbon::now()->format('Y-m-d H:i:s'),
        ]);

        session()->forget('validasi_no_doc');

        return redirect()->route('karantina.validasi.index')->with('success', 'Data berhasil di-reject!');
    }
}
