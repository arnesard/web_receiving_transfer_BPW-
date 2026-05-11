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

        // Query Utama
        // Status: 'selesai', 'diterima', 'sebagian' (biar yang sudah sampai juga muncul)
        $query = TransferRak::with([
            'karyawan:id,name',
            'supir:id,nama_karyawan',
            'mobil:id,nama_kendaraan',
            'details.karyawanPengirim:id,name', // Ambil data siapa yang scan tiap rak
            'details.penerima:id,name' // Ambil data siapa yang terima tiap rak
        ])
            ->whereIn('status', ['selesai', 'diterima', 'sebagian', 'batal'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);

        if ($operator) {
            // Cek apakah operator ini yang start transfer ATAU yang ikutan scan rak di detail
            $query->where(function ($q) use ($operator) {
                $q->where('id_karyawan', $operator)
                    ->orWhereHas('details', function ($sq) use ($operator) {
                        $sq->where('id_karyawan_pengirim', $operator);
                    });
            });
        }
        if ($supir) $query->where('id_supir', $supir);
        if ($kendaraan) $query->where('id_mobil', $kendaraan);

        $transfers = $query->orderByDesc('created_at')->get();

        // ── RINGKASAN (KPI) ──
        $done = $transfers->whereIn('status', ['selesai', 'diterima', 'sebagian']);
        $totalTransfer = $transfers->count();

        // Hitung total rak (Isi + Kosong)
        $totalRak = $done->sum('total_rak') + $done->sum('jumlah_rak_kosong');

        $avgDurasi = $done
            ->filter(fn($t) => $t->waktu_mulai && $t->waktu_selesai)
            ->avg(fn($t) => $t->waktu_mulai->diffInMinutes($t->waktu_selesai));

        // ── FORMAT DATA TABEL ──
        $rows = $transfers->map(function ($t, $idx) {
            // Ambil semua nama operator yang terlibat di transfer ini
            $names = $t->details->pluck('karyawanPengirim.name')->filter()->unique()->toArray();
            if ($t->karyawan) array_unshift($names, $t->karyawan->name);
            $allOperators = implode(', ', array_unique($names));

            // Ambil semua nama penerima unik dari detail
            $receiverNames = [];

            if ($t->tipe === 'rak_kosong') {
                // ambil dari penerima header (tab terima)
                $receiverNames = $t->penerima ? [$t->penerima->name] : [];
            } else {
                // rak isi: dari detail
                $receiverNames = $t->details
                    ->pluck('penerima.name')
                    ->filter()
                    ->unique()
                    ->toArray();
            }
            $allReceivers = implode(', ', $receiverNames);

            $durasi = ($t->waktu_mulai && $t->waktu_selesai)
                ? $t->waktu_mulai->diffInMinutes($t->waktu_selesai) . ' mnt'
                : '-';

            // Keterangan Rak (Bedakan Isi vs Kosong)
            $labelRak = $t->total_rak . ' Rak';
            if ($t->tipe === 'rak_kosong') {
                $labelRak = $t->jumlah_rak_kosong . ' Rak, ' . $t->jumlah_palet_kosong . ' Palet (KOSONG)';
            }

            return [
                'id'            => $t->id,
                'no'            => $idx + 1,
                'tanggal'       => $t->created_at->format('d/m/Y'),
                'operator'      => $allOperators ?: '-',
                'penerima'      => $allReceivers ?: '-',
                'supir'         => $t->supir?->nama_karyawan ?? '-',
                'kendaraan'     => $t->mobil?->nama_kendaraan ?? '-',
                'total_rak'     => $labelRak,
                'waktu_mulai'   => $t->waktu_mulai ? $t->waktu_mulai->format('H:i') : '-',
                'waktu_selesai' => $t->waktu_selesai ? $t->waktu_selesai->format('H:i') : '-',
                'durasi'        => $durasi,
                'status'        => $t->status,
                'catatan'       => $t->catatan ?? '-',
            ];
        });

        return response()->json([
            'ringkasan' => [
                'total_transfer' => $totalTransfer,
                'total_rak'      => (int) $totalRak,
                'avg_durasi'     => $avgDurasi ? round($avgDurasi) . ' mnt' : '-',
                'success_rate'   => $totalTransfer > 0 ? round(($done->count() / $totalTransfer) * 100) : 0,
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
            'details.karyawanPengirim:id,name',
            'details.penerima:id,name',
        ])->findOrFail($id);

        $durasi = ($transfer->waktu_mulai && $transfer->waktu_selesai)
            ? $transfer->waktu_mulai->diffInMinutes($transfer->waktu_selesai) . ' menit'
            : '-';

        $details = $transfer->details->map(function ($d, $idx) {
            return [
                'no'         => $idx + 1,
                'kode_rak'   => $d->kode_rak,
                'operator'   => $d->karyawanPengirim?->name ?? '-',
                'waktu_scan' => $d->waktu_scan ? Carbon::parse($d->waktu_scan)->format('H:i:s') : '-',
                'lokasi_terima' => $d->lokasi_diterima ?? '-',
                'waktu_terima'  => $d->waktu_diterima ? Carbon::parse($d->waktu_diterima)->format('H:i:s') : 'Belum',
                'penerima'      => $d->penerima?->name ?? '-',
            ];
        });

        return response()->json([
            'header' => [
                'tanggal'       => $transfer->created_at->format('d/m/Y'),
                'operator'      => $transfer->karyawan?->name ?? '-',
                'supir'         => $transfer->supir?->nama_karyawan ?? '-',
                'kendaraan'     => $transfer->mobil?->nama_kendaraan ?? '-',
                'total_rak'     => ($transfer->tipe === 'rak_kosong')
                    ? $transfer->jumlah_rak_kosong . ' Rak / ' . $transfer->jumlah_palet_kosong . ' Palet'
                    : $transfer->total_rak . ' Rak',
                'waktu_mulai'   => $transfer->waktu_mulai?->format('H:i:s') ?? '-',
                'waktu_selesai' => $transfer->waktu_selesai?->format('H:i:s') ?? '-',
                'durasi'        => $durasi,
                'status'        => strtoupper($transfer->status),
                'catatan'       => $transfer->catatan ?? '-',
            ],
            'details' => $details,
        ]);
    }
}
