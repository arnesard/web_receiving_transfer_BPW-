@extends('MonitoringTransferRak.app')
@section('title', 'Laporan Transfer Rak')

@push('styles')
    <style>
        body {
            overflow-y: auto !important;
        }

        .lap-wrap {
            padding: 14px 14px 80px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header */
        .lap-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .lap-title {
            font-size: 16px;
            font-weight: 800;
            color: #64c8ff;
            text-shadow: 0 0 10px rgba(100, 200, 255, 0.3);
        }

        /* Filter */
        .filter-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(59, 130, 246, 0.15);
            border-radius: 14px;
            padding: 14px;
            margin-bottom: 16px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .filter-group.full {
            grid-column: span 2;
        }

        .filter-label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .filter-input,
        .filter-select {
            width: 100%;
            padding: 9px 12px;
            background: rgba(59, 130, 246, 0.08);
            border: 1px solid rgba(59, 130, 246, 0.25);
            border-radius: 8px;
            color: #e2e8f0;
            font-size: 13px;
            outline: none;
            transition: border-color 0.2s;
            appearance: none;
        }

        .filter-input:focus,
        .filter-select:focus {
            border-color: #60a5fa;
        }

        .filter-actions {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        .btn-filter {
            flex: 1;
            padding: 10px;
            border-radius: 8px;
            border: none;
            font-weight: 700;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-search {
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            color: white;
        }

        .btn-search:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-export {
            background: rgba(34, 197, 94, 0.12);
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.25);
        }

        .btn-export:hover {
            background: rgba(34, 197, 94, 0.2);
        }

        .btn-reset {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .btn-reset:hover {
            background: rgba(239, 68, 68, 0.18);
        }

        /* KPI */
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 16px;
        }

        .kpi-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(59, 130, 246, 0.15);
            border-radius: 14px;
            padding: 14px;
            position: relative;
            overflow: hidden;
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--kc);
            opacity: 0.7;
        }

        .kpi-icon {
            font-size: 20px;
            margin-bottom: 4px;
        }

        .kpi-label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .kpi-value {
            font-size: 28px;
            font-weight: 900;
            color: var(--kc);
            line-height: 1;
        }

        .kpi-sub {
            font-size: 10px;
            color: #475569;
            margin-top: 4px;
        }

        /* Table */
        .table-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(59, 130, 246, 0.15);
            border-radius: 14px;
            padding: 14px;
            overflow-x: auto;
        }

        .table-title {
            font-size: 12px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 12px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            min-width: 700px;
        }

        .data-table th {
            background: rgba(59, 130, 246, 0.1);
            color: #64c8ff;
            font-weight: 700;
            padding: 10px 8px;
            text-align: left;
            border-bottom: 1px solid rgba(59, 130, 246, 0.2);
            white-space: nowrap;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .data-table td {
            padding: 10px 8px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            color: #cbd5e1;
        }

        .data-table tr:hover td {
            background: rgba(59, 130, 246, 0.05);
        }

        .btn-detail {
            padding: 5px 10px;
            border-radius: 6px;
            border: 1px solid rgba(59, 130, 246, 0.3);
            background: rgba(59, 130, 246, 0.1);
            color: #64c8ff;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-detail:hover {
            background: rgba(59, 130, 246, 0.2);
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #475569;
            font-size: 13px;
        }

        .badge-selesai {
            color: #4ade80;
        }

        .badge-batal {
            color: #f87171;
        }

        /* Modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.75);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 500;
            padding: 16px;
        }

        .modal-overlay.hidden {
            display: none;
        }

        .modal-box {
            background: #111827;
            border: 1px solid rgba(59, 130, 246, 0.25);
            border-radius: 16px;
            padding: 20px;
            width: 95%;
            max-width: 800px;
            /* Lebarin modal biar muat kolom baru */
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 14px;
        }

        .modal-title {
            font-size: 15px;
            font-weight: 700;
            color: #64c8ff;
        }

        .modal-close {
            background: none;
            border: none;
            color: #64748b;
            font-size: 20px;
            cursor: pointer;
        }

        .modal-close:hover {
            color: #f87171;
        }

        .modal-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 14px;
        }

        .modal-info-item {
            background: rgba(59, 130, 246, 0.06);
            border-radius: 8px;
            padding: 8px 10px;
        }

        .modal-info-label {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
        }

        .modal-info-value {
            font-size: 12px;
            font-weight: 600;
            color: #e2e8f0;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .detail-table th {
            background: rgba(34, 197, 94, 0.1);
            color: #4ade80;
            padding: 8px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }

        .detail-table td {
            padding: 8px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            color: #cbd5e1;
        }

        .detail-table .rak-code {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: #22c55e;
        }

        /* Loading */
        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(100, 200, 255, 0.3);
            border-top-color: #64c8ff;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @media (min-width: 768px) {
            .filter-grid {
                grid-template-columns: repeat(4, 1fr);
            }

            .filter-group.full {
                grid-column: span 4;
            }

            .kpi-row {
                grid-template-columns: repeat(4, 1fr);
            }

            .lap-title {
                font-size: 18px;
            }
        }
    </style>
@endpush

@section('content')
    {{-- MODAL DETAIL --}}
    <div class="modal-overlay hidden" id="detailModal">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title">📦 Detail Rak Transfer</div>
                <button class="modal-close" onclick="closeDetail()">&times;</button>
            </div>
            <div class="modal-info" id="modalInfo"></div>
            <div style="overflow-x:auto;">
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Rak</th>
                            <th>Pengirim</th>
                            <th>Waktu Kirim</th>
                            <th>Penerima</th>
                            <th>Lokasi</th>
                            <th>Waktu Terima</th>
                        </tr>
                    </thead>
                    <tbody id="modalBody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="lap-wrap">
        {{-- HEADER --}}
        <div class="lap-header">
            <div class="lap-title">📋 Laporan Transfer Rak</div>
        </div>

        {{-- FILTER --}}
        <div class="filter-card">
            <div class="filter-grid">
                <div class="filter-group">
                    <label class="filter-label">Tanggal Mulai</label>
                    <input type="date" class="filter-input" id="fStartDate">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Tanggal Akhir</label>
                    <input type="date" class="filter-input" id="fEndDate">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Operator</label>
                    <select class="filter-select" id="fOperator">
                        <option value="">Semua</option>
                        @foreach ($operators as $o)
                            <option value="{{ $o->id }}">{{ $o->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Supir</label>
                    <select class="filter-select" id="fSupir">
                        <option value="">Semua</option>
                        @foreach ($drivers as $d)
                            <option value="{{ $d->id }}">{{ $d->nama_karyawan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group full">
                    <label class="filter-label">Kendaraan</label>
                    <select class="filter-select" id="fKendaraan">
                        <option value="">Semua</option>
                        @foreach ($vehicles as $v)
                            <option value="{{ $v->id }}">{{ $v->nama_kendaraan }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="filter-actions">
                <button class="btn-filter btn-search" onclick="loadData()">🔍 Cari</button>
                <button class="btn-filter btn-export" onclick="exportExcel()">📥 Export Excel</button>
                <button class="btn-filter btn-reset" onclick="resetFilter()">↺ Reset</button>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="table-card">
            <div class="table-title">📄 Data Transfer Rak</div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Operator</th>
                        <th>Supir</th>
                        <th>Kendaraan</th>
                        <th>Total Rak</th>
                        <th>Mulai</th>
                        <th>Selesai</th>
                        <th>Durasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr>
                        <td colspan="10" class="empty-state">Tekan "Cari" untuk menampilkan data</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

<script src="{{ asset('js/xlsx.full.min.js') }}"></script>
<script>
    let reportData = [];

    // Set default dates
    document.addEventListener('DOMContentLoaded', () => {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('fStartDate').value = today;
        document.getElementById('fEndDate').value = today;
        loadData();
    });

    async function loadData() {
        const params = new URLSearchParams({
            start_date: document.getElementById('fStartDate').value,
            end_date: document.getElementById('fEndDate').value,
            operator: document.getElementById('fOperator').value,
            supir: document.getElementById('fSupir').value,
            kendaraan: document.getElementById('fKendaraan').value,
        });

        document.getElementById('tableBody').innerHTML =
            '<tr><td colspan="10" class="empty-state"><span class="loading-spinner"></span> Memuat data...</td></tr>';

        try {
            const res = await fetch(`/transfer-rak/laporan/data?${params}`);
            const json = await res.json();

            reportData = json.data;
            updateKPI(json.ringkasan);
            renderTable(json.data);
        } catch (e) {
            document.getElementById('tableBody').innerHTML =
                '<tr><td colspan="10" class="empty-state">❌ Gagal memuat data</td></tr>';
        }
    }

    function updateKPI(r) {
        if (!r) return;

        const el1 = document.getElementById('kpiTransfer');
        const el2 = document.getElementById('kpiRak');
        const el3 = document.getElementById('kpiDurasi');
        const el4 = document.getElementById('kpiRate');

        if (el1) el1.textContent = r.total_transfer;
        if (el2) el2.textContent = r.total_rak;
        if (el3) el3.textContent = r.avg_durasi;
        if (el4) el4.textContent = r.success_rate + '%';
    }

    function renderTable(data) {
        const tbody = document.getElementById('tableBody');
        if (!data.length) {
            tbody.innerHTML = '<tr><td colspan="10" class="empty-state">Tidak ada data ditemukan</td></tr>';
            return;
        }
        tbody.innerHTML = data.map((d, i) => `
        <tr>
            <td>${i + 1}</td>
            <td>${d.tanggal}</td>
            <td>${d.operator}</td>
            <td>${d.supir}</td>
            <td>${d.kendaraan}</td>
            <td style="font-weight:700;color:#64c8ff">${d.total_rak}</td>
            <td>${d.waktu_mulai}</td>
            <td>${d.waktu_selesai}</td>
            <td>${d.durasi}</td>
            <td><button class="btn-detail" onclick="openDetail(${d.id})">👁 Detail</button></td>
        </tr>
    `).join('');
    }

    async function openDetail(id) {
        const modal = document.getElementById('detailModal');
        modal.classList.remove('hidden');
        document.getElementById('modalInfo').innerHTML =
            '<div class="empty-state"><span class="loading-spinner"></span></div>';
        document.getElementById('modalBody').innerHTML = '';

        try {
            const res = await fetch(`/transfer-rak/laporan/detail/${id}`);
            const json = await res.json();
            const h = json.header;

            document.getElementById('modalInfo').innerHTML = `
            <div class="modal-info-item"><div class="modal-info-label">Tanggal</div><div class="modal-info-value">${h.tanggal}</div></div>
            <div class="modal-info-item"><div class="modal-info-label">Operator</div><div class="modal-info-value">${h.operator}</div></div>
            <div class="modal-info-item"><div class="modal-info-label">Supir</div><div class="modal-info-value">${h.supir}</div></div>
            <div class="modal-info-item"><div class="modal-info-label">Kendaraan</div><div class="modal-info-value">${h.kendaraan}</div></div>
            <div class="modal-info-item"><div class="modal-info-label">Durasi</div><div class="modal-info-value">${h.durasi}</div></div>
            <div class="modal-info-item"><div class="modal-info-label">Total Rak</div><div class="modal-info-value" style="color:#22c55e;font-size:16px">${h.total_rak}</div></div>
        `;

            document.getElementById('modalBody').innerHTML = json.details.map(d => `
            <tr>
                <td>${d.no}</td>
                <td class="rak-code">${d.kode_rak}</td>
                <td style="font-size:11px">${d.operator}</td>
                <td style="font-size:11px">${d.waktu_scan}</td>
                <td style="font-size:11px;color:#4ade80">${d.penerima}</td>
                <td style="font-size:11px">${d.lokasi_terima}</td>
                <td style="font-size:11px;color:#4ade80">${d.waktu_terima}</td>
            </tr>
        `).join('');
        } catch (e) {
            document.getElementById('modalInfo').innerHTML = '<div class="empty-state">❌ Gagal memuat detail</div>';
        }
    }

    function closeDetail() {
        document.getElementById('detailModal').classList.add('hidden');
    }

    // Close modal on overlay click
    document.getElementById('detailModal').addEventListener('click', function(e) {
        if (e.target === this) closeDetail();
    });

    function resetFilter() {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('fStartDate').value = today;
        document.getElementById('fEndDate').value = today;
        document.getElementById('fOperator').value = '';
        document.getElementById('fSupir').value = '';
        document.getElementById('fKendaraan').value = '';
        loadData();
    }

    function exportExcel() {
        if (!reportData.length) {
            alert('Tidak ada data untuk di-export');
            return;
        }

        const header = ['No', 'Tanggal', 'Operator', 'Supir', 'Kendaraan', 'Total Rak', 'Mulai', 'Selesai', 'Durasi'];
        const rows = reportData.map((d, i) => [
            i + 1, d.tanggal, d.operator, d.supir, d.kendaraan,
            d.total_rak, d.waktu_mulai, d.waktu_selesai, d.durasi
        ]);

        const wsData = [header, ...rows];
        const wb = XLSX.utils.book_new();
        const ws = XLSX.utils.aoa_to_sheet(wsData);

        // Column widths
        ws['!cols'] = [{
                wch: 5
            }, {
                wch: 12
            }, {
                wch: 20
            }, {
                wch: 20
            }, {
                wch: 18
            },
            {
                wch: 10
            }, {
                wch: 8
            }, {
                wch: 8
            }, {
                wch: 10
            }
        ];

        XLSX.utils.book_append_sheet(wb, ws, 'Laporan Transfer Rak');

        const startDate = document.getElementById('fStartDate').value;
        const endDate = document.getElementById('fEndDate').value;
        XLSX.writeFile(wb, `laporan_transfer_rak_${startDate}_${endDate}.xlsx`);
    }
</script>
