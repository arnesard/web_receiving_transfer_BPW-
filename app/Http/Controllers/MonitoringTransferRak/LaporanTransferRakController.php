<?php

namespace App\Http\Controllers\MonitoringTransferRak;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MonitoringTransferRak\TransferRak;
use App\Models\MonitoringTransferRak\Driver;
use App\Models\MonitoringTransferRak\Vehicle;
use App\Models\Employee;
use Carbon\Carbon;

class LaporanTransferRakController extends Controller
{
    /**
     * Tampilkan halaman laporan
     */
    public function index()
    {
        $operators  = Employee::orderBy('name')->get(['id', 'name']);
        $drivers    = Driver::orderBy('nama_karyawan')->get(['id', 'nama_karyawan']);
        $vehicles   = Vehicle::orderBy('nama_kendaraan')->get(['id', 'nama_kendaraan']);

        return view('MonitoringTransferRak.laporan', compact('operators', 'drivers', 'vehicles'));
    }

    /**
     * API: Data laporan terfilter + ringkasan
     */
    public function getData(Request $request)
    {
        $startDate  = $request->get('start_date', Carbon::today()->format('Y-m-d'));
        $endDate    = $request->get('end_date', Carbon::today()->format('Y-m-d'));
        $operator   = $request->get('operator', '');
        $supir      = $request->get('supir', '');
        $kendaraan  = $request->get('kendaraan', '');

        $query = TransferRak::with([
            'karyawan:id,name',
            'supir:id,nama_karyawan',
            'mobil:id,nama_kendaraan',
        ])
            ->whereIn('status', ['selesai', 'batal'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        if ($operator) {
            $query->where('id_karyawan', $operator);
        }
        if ($supir) {
            $query->where('id_supir', $supir);
        }
        if ($kendaraan) {
            $query->where('id_mobil', $kendaraan);
        }

        $transfers = $query->orderByDesc('created_at')->limit(500)->get();

        // ── RINGKASAN ──
        $selesai = $transfers->where('status', 'selesai');
        $totalTransfer  = $transfers->count();
        $totalRak       = $selesai->sum('total_rak');
        $transferDone   = $selesai->count();
        $batal          = $transfers->where('status', 'batal')->count();
        $allDone        = $transferDone + $batal;
        $successRate    = $allDone > 0 ? round(($transferDone / $allDone) * 100) : 0;

        $avgDurasi = $selesai
            ->filter(fn($t) => $t->waktu_mulai && $t->waktu_selesai)
            ->avg(fn($t) => $t->waktu_mulai->diffInMinutes($t->waktu_selesai));

        // ── FORMAT DATA TABEL ──
        $rows = $transfers->map(function ($t, $idx) {
            $durasi = ($t->waktu_mulai && $t->waktu_selesai)
                ? $t->waktu_mulai->diffInMinutes($t->waktu_selesai) . ' mnt'
                : '-';

            return [
                'id'          => $t->id,
                'no'          => $idx + 1,
                'tanggal'     => $t->created_at->format('d/m/Y'),
                'operator'    => $t->karyawan?->name ?? '-',
                'supir'       => $t->supir?->nama_karyawan ?? '-',
                'kendaraan'   => $t->mobil?->nama_kendaraan ?? '-',
                'total_rak'   => $t->total_rak,
                'waktu_mulai' => $t->waktu_mulai ? $t->waktu_mulai->format('H:i') : '-',
                'waktu_selesai' => $t->waktu_selesai ? $t->waktu_selesai->format('H:i') : '-',
                'durasi'      => $durasi,
                'status'      => $t->status,
                'catatan'     => $t->catatan ?? '-',
            ];
        });

        return response()->json([
            'ringkasan' => [
                'total_transfer' => $totalTransfer,
                'total_rak'      => (int) $totalRak,
                'avg_durasi'     => $avgDurasi ? round($avgDurasi) . ' mnt' : '-',
                'success_rate'   => $successRate,
            ],
            'data' => $rows,
        ]);
    }

    /**
     * API: Detail rak per transfer
     */
    public function getDetail($id)
    {
        $transfer = TransferRak::with([
            'karyawan:id,name',
            'supir:id,nama_karyawan',
            'mobil:id,nama_kendaraan',
        ])->findOrFail($id);

        $durasi = ($transfer->waktu_mulai && $transfer->waktu_selesai)
            ? $transfer->waktu_mulai->diffInMinutes($transfer->waktu_selesai) . ' menit'
            : '-';

        return response()->json([
            'header' => [
                'tanggal'       => $transfer->created_at->format('d/m/Y'),
                'operator'      => $transfer->karyawan?->name ?? '-',
                'supir'         => $transfer->supir?->nama_karyawan ?? '-',
                'kendaraan'     => $transfer->mobil?->nama_kendaraan ?? '-',
                'total_rak'     => $transfer->total_rak,
                'waktu_mulai'   => $transfer->waktu_mulai?->format('H:i:s') ?? '-',
                'waktu_selesai' => $transfer->waktu_selesai?->format('H:i:s') ?? '-',
                'durasi'        => $durasi,
                'catatan'       => $transfer->catatan ?? '-',
            ],
            'details' => [
                [
                    'no' => 1,
                    'kode_rak' => '-',
                    'waktu_scan' => '-'
                ]
            ],
        ]);
    }
}
