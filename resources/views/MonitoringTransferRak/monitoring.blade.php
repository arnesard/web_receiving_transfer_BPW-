@extends('MonitoringTransferRak.app')

@section('title', 'Transfer Rak')

@push('styles')
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            height: 100%;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: radial-gradient(circle at 20% 20%, rgba(59, 130, 246, 0.25), transparent 45%),
                radial-gradient(circle at 80% 10%, rgba(99, 102, 241, 0.18), transparent 40%),
                #0b1220;
            background-attachment: fixed;
            color: #e2e8f0;
            overflow: hidden;
        }

        .app-wrap {
            width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── HEADER & TABS ── */
        .header {
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.95), rgba(15, 23, 42, 0.7));
            border-bottom: 1px solid rgba(59, 130, 246, 0.2);
            flex-shrink: 0;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 14px;
        }

        .app-title {
            font-size: 14px;
            font-weight: 800;
            color: #64c8ff;
            text-shadow: 0 0 10px rgba(100, 200, 255, 0.3);
        }

        .operator-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(59, 130, 246, 0.12);
            border: 1px solid rgba(59, 130, 246, 0.25);
            border-radius: 20px;
            padding: 4px 10px;
            font-size: 12px;
            color: #94a3b8;
            cursor: pointer;
            transition: all 0.2s;
        }

        .operator-badge span {
            color: #64c8ff;
            font-weight: 600;
            max-width: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .tab-container {
            display: flex;
            padding: 0 14px 10px;
            gap: 10px;
        }

        .tab-btn {
            flex: 1;
            padding: 12px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 1px solid rgba(59, 130, 246, 0.3);
            background: rgba(59, 130, 246, 0.1);
            color: #94a3b8;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }

        .tab-btn.active {
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            color: white;
            border-color: transparent;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }

        .tab-content {
            display: none;
            flex: 1;
            flex-direction: column;
            overflow: hidden;
        }

        .tab-content.active {
            display: flex;
        }

        /* ── SETUP PANEL (KIRIM & TERIMA) ── */
        .setup-panel {
            background: rgba(15, 23, 42, 0.6);
            padding: 12px 14px 8px;
            flex-shrink: 0;
            transition: all 0.3s ease;
            overflow-y: auto;
        }

        .setup-panel.hidden {
            display: none;
        }

        .setup-row {
            margin-bottom: 10px;
        }

        .setup-label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 4px;
            display: block;
        }

        .setup-input,
        .setup-select {
            width: 100%;
            padding: 10px 12px;
            background: rgba(59, 130, 246, 0.08);
            border: 1px solid rgba(59, 130, 246, 0.25);
            border-radius: 10px;
            color: #e2e8f0;
            font-size: 14px;
            outline: none;
            appearance: none;
        }

        .setup-select option {
            background: #0f172a;
            color: #e2e8f0;
        }

        .setup-input:focus,
        .setup-select:focus {
            border-color: #60a5fa;
            background: rgba(59, 130, 246, 0.13);
        }

        .input-hint {
            font-size: 10px;
            color: #475569;
            margin-top: 3px;
        }

        /* ── SCAN AREA (KIRIM) ── */
        .scan-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 12px 14px;
            gap: 10px;
            overflow: hidden;
        }

        .scan-input-wrap {
            background: rgba(59, 130, 246, 0.08);
            border: 2px solid rgba(59, 130, 246, 0.3);
            border-radius: 12px;
            padding: 10px 12px;
            flex-shrink: 0;
        }

        #scanInput {
            width: 100%;
            padding: 12px 14px;
            background: rgba(59, 130, 246, 0.1);
            border: 2px solid rgba(59, 130, 246, 0.35);
            border-radius: 8px;
            color: #e2e8f0;
            font-size: 16px;
            text-align: center;
            font-weight: 600;
            letter-spacing: 1px;
            outline: none;
        }

        #scanInput:disabled {
            opacity: 0.35;
            cursor: not-allowed;
        }

        .counter-row {
            display: flex;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .counter-box {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(99, 102, 241, 0.1));
            border: 1px solid rgba(100, 200, 255, 0.25);
            border-radius: 10px;
            padding: 10px;
            text-align: center;
            flex: 1;
        }

        .counter-label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
        }

        .counter-value {
            font-size: 32px;
            font-weight: 900;
            color: #64c8ff;
        }

        .scan-list {
            flex: 1;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .scan-item {
            background: rgba(34, 197, 94, 0.08);
            border: 1px solid rgba(34, 197, 94, 0.2);
            border-radius: 8px;
            padding: 9px 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* ── BUTTONS ── */
        .mulai-footer {
            padding: 10px 14px;
            background: rgba(11, 18, 32, 0.98);
            border-top: 1px solid rgba(59, 130, 246, 0.15);
            flex-shrink: 0;
        }

        .mulai-footer.hidden {
            display: none;
        }

        .btn-start {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
        }

        .btn-start:disabled {
            background: rgba(255, 255, 255, 0.06);
            color: #475569;
            cursor: not-allowed;
        }

        .center-actions {
            display: none;
            gap: 12px;
            padding: 8px 14px;
            flex-shrink: 0;
        }

        .center-actions.visible {
            display: flex;
        }

        .btn-center {
            flex: 1;
            padding: 12px;
            border-radius: 12px;
            border: none;
            font-weight: 800;
            font-size: 15px;
            cursor: pointer;
        }

        .btn-cancel {
            background: rgba(239, 68, 68, 0.13);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.35);
        }

        .btn-finish {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: white;
        }

        .btn-finish:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* ── DETAIL TERIMA ── */
        .terima-detail {
            background: rgba(34, 197, 94, 0.05);
            border: 1px solid rgba(34, 197, 94, 0.2);
            border-radius: 12px;
            padding: 12px;
            margin-top: 10px;
            display: none;
        }

        .terima-detail.visible {
            display: block;
        }

        .td-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 12px;
        }

        .td-label {
            color: #94a3b8;
        }

        .td-val {
            font-weight: 600;
            color: #e2e8f0;
        }

        .toast {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            padding: 11px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            z-index: 999;
        }

        .toast.error {
            background: #fee2e2;
            color: #ef4444;
        }

        .toast.success {
            background: #dcfce7;
            color: #22c55e;
        }

        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 500;
            padding: 20px;
        }

        .modal-overlay.hidden {
            display: none;
        }

        .modal-box {
            background: #111827;
            border: 1px solid rgba(59, 130, 246, 0.25);
            border-radius: 16px;
            padding: 20px;
            width: 100%;
            max-width: 340px;
        }

        .modal-title {
            font-size: 15px;
            font-weight: 700;
            color: #64c8ff;
            margin-bottom: 14px;
            text-align: center;
        }

        .modal-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
        }

        .setup-select {
            background-image: linear-gradient(45deg, transparent 50%, #60a5fa 50%),
                linear-gradient(135deg, #60a5fa 50%, transparent 50%);
            background-position: calc(100% - 18px) calc(1em + 2px),
                calc(100% - 13px) calc(1em + 2px);
            background-size: 5px 5px, 5px 5px;
            background-repeat: no-repeat;
        }

        .operator-item {
            padding: 10px 12px;
            border-radius: 10px;
            background: rgba(59, 130, 246, 0.08);
            border: 1px solid rgba(59, 130, 246, 0.2);
            color: #e2e8f0;
            cursor: pointer;
            transition: 0.2s;
        }

        .operator-item:hover {
            background: rgba(59, 130, 246, 0.2);
            border-color: #60a5fa;
            box-shadow: 0 0 10px rgba(96, 165, 250, 0.3);
        }
    </style>
@endpush

{{-- MODAL PILIH OPERATOR --}}
<div class="modal-overlay hidden" id="operatorModal">
    <div class="modal-box">
        <div class="modal-title">👤 Pilih Operator (Login)</div>
        <select class="setup-select" id="modalKaryawanSelect" style="margin-bottom:12px;">
            <option value="">-- Pilih Operator --</option>
            @foreach ($karyawan as $k)
                <option value="{{ $k->id }}" data-nama="{{ $k->name }}">{{ $k->name }}</option>
            @endforeach
        </select>
        <button class="modal-btn" id="btnConfirmOperator">Konfirmasi</button>
    </div>
</div>

<div class="app-wrap">
    <div class="header">
        <div class="header-top">
            <div class="app-title">📦 Transfer Rak</div>
            <div class="operator-badge" onclick="openOperatorModal()">
                👤 <span id="operatorName">Pilih Operator</span> ✏️
            </div>
        </div>
        <div class="tab-container">
            <div class="tab-btn active" id="tabKirim" onclick="switchTab('kirim')">📤 KIRIM</div>
            <div class="tab-btn" id="tabTerima" onclick="switchTab('terima')">📥 TERIMA</div>
        </div>
    </div>

    {{-- TAB KIRIM --}}
    <div class="tab-content active" id="contentKirim">
        <div class="setup-panel" id="setupKirim">
            <div class="setup-row">
                <label class="setup-label">🚗 Nama Supir</label>
                <input list="driverList" class="setup-input" id="supirInput" placeholder="Ketik/pilih supir..."
                    autocomplete="off">
                <datalist id="driverList"></datalist>
            </div>
            <div class="setup-row">
                <label class="setup-label">🏢 Lokasi Asal</label>
                <select class="setup-select" id="lokasiAsalInput">
                    <option value="">Pilih Lokasi Asal...</option>
                    <option value="Plant B">Plant B</option>
                    <option value="Plant H">Plant H</option>
                    <option value="Plant I">Plant I</option>
                    <option value="Plant T">Plant T</option>
                    <option value="BPW 1">BPW 1</option>
                    <option value="BPW 2">BPW 2</option>
                    <option value="BPW 3">BPW 3</option>
                    <option value="Gudang Bahan">Gudang Bahan</option>
                </select>
                </select>
            </div>
            <div class="setup-row" style="margin-bottom:0">
                <label class="setup-label">🚙 Scan Barcode Kendaraan</label>
                <input type="text" class="setup-input" id="mobilKirimInput"
                    placeholder="Arahkan scanner ke kendaraan...">
            </div>
            <div class="setup-row">
                <label class="setup-label">📝 Catatan (Opsional)</label>
                <textarea class="setup-input" id="catatanInput" rows="2" placeholder="Tambahkan catatan jika perlu..."></textarea>
            </div>
        </div>

        <div class="mulai-footer" id="footerKirim">
            <button class="btn-start" id="btnMulai" disabled>MULAI TRANSFER</button>
        </div>

        <div class="scan-area" id="scanAreaKirim" style="display:none;">
            <audio id="beepOk" preload="auto">
                <source src="{{ asset('sounds/WindowsDing.wav') }}" type="audio/wav">
            </audio>

            <div class="scan-input-wrap">
                <label class="setup-label">🔍 Scan Barcode Rak</label>
                <input type="text" id="scanInput" placeholder="Arahkan scanner ke barcode rak..." autocomplete="off">
            </div>
            <div class="counter-row">
                <div class="counter-box">
                    <div class="counter-label">Total Rak</div>
                    <div class="counter-value" id="totalCount">0</div>
                </div>
            </div>
            <div class="center-actions visible">
                <button class="btn-center btn-cancel" id="btnCancelKirim">Batal</button>
                <button class="btn-center btn-finish" id="btnFinishKirim" disabled>Selesai</button>
            </div>
            <div class="scan-list" id="scanList"></div>
        </div>
    </div>

    {{-- TAB TERIMA --}}
    <div class="tab-content" id="contentTerima">
        <div class="setup-panel">
            <div class="setup-row">
                <label class="setup-label">🚙 Scan Barcode Kendaraan Datang</label>
                <input type="text" class="setup-input" id="mobilTerimaInput" placeholder="Scan kendaraan...">
            </div>

            <div class="terima-detail" id="terimaDetail">
                <div class="td-row"><span class="td-label">Status:</span> <span class="td-val"
                        style="color:#4ade80;">Ditemukan</span></div>
                <div class="td-row"><span class="td-label">Pengirim:</span> <span class="td-val"
                        id="tdPengirim">-</span></div>
                <div class="td-row"><span class="td-label">Supir:</span> <span class="td-val"
                        id="tdSupir">-</span></div>
                <div class="td-row"><span class="td-label">Asal:</span> <span class="td-val" id="tdAsal">-</span>
                </div>
                <div class="td-row"><span class="td-label">Total Rak:</span> <span class="td-val"
                        id="tdTotal">-</span></div>
                <div class="td-row"><span class="td-label">Waktu:</span> <span class="td-val"
                        id="tdWaktu">-</span></div>
                <div class="td-row">
                    <span class="td-label">Catatan:</span>
                    <span class="td-val" id="tdCatatan">-</span>
                </div>

                <hr style="border:0; border-top:1px solid rgba(255,255,255,0.1); margin:10px 0;">

                <div class="setup-row">
                    <label class="setup-label">🏢 Diterima di Lokasi</label>
                    <select class="setup-select" id="lokasiTujuanInput">
                        <option value="">Pilih Lokasi Diterima...</option>
                        <option value="Plant B">Plant B</option>
                        <option value="Plant H">Plant H</option>
                        <option value="Plant I">Plant I</option>
                        <option value="Plant T">Plant T</option>
                        <option value="BPW 1">BPW 1</option>
                        <option value="BPW 2">BPW 2</option>
                        <option value="BPW 3">BPW 3</option>
                        <option value="Gudang Bahan">Gudang Bahan</option>
                    </select>
                    </select>
                </div>
                <div class="setup-row">
                    <label class="setup-label">👤 Diterima Oleh</label>
                    <select class="setup-select" id="penerimaInput">
                        <option value="">-- Pilih Penerima --</option>
                        @foreach ($karyawan as $k)
                            <option value="{{ $k->id }}">{{ $k->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button class="btn-start" id="btnProsesTerima" style="margin-top:10px;" disabled>SELESAIKAN
                    PENERIMAAN</button>
            </div>
        </div>
    </div>
</div>

<script>
    const CSRF = '{{ csrf_token() }}';
    const state = {
        operatorId: null,
        operatorName: null,
        transferId: null,
        terimaTransferId: null,
        scanList: []
    };

    function switchTab(tab) {
        document.getElementById('tabKirim').classList.toggle('active', tab === 'kirim');
        document.getElementById('tabTerima').classList.toggle('active', tab === 'terima');
        document.getElementById('contentKirim').classList.toggle('active', tab === 'kirim');
        document.getElementById('contentTerima').classList.toggle('active', tab === 'terima');
    }

    function showToast(msg, type = 'error') {
        const t = document.createElement('div');
        t.className = `toast ${type}`;
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(() => t.remove(), 3000);
    }

    // ── INIT ──
    document.addEventListener('DOMContentLoaded', () => {
        loadDrivers();
    });

    function openOperatorModal() {
        document.getElementById('operatorModal').classList.remove('hidden');
    }

    function closeOperatorModal() {
        document.getElementById('operatorModal').classList.add('hidden');
    }

    document.getElementById('btnConfirmOperator').addEventListener('click', () => {
        const sel = document.getElementById('modalKaryawanSelect');
        if (!sel.value) {
            showToast('Pilih operator!');
            return;
        }
        state.operatorId = sel.value;
        state.operatorName = sel.options[sel.selectedIndex].dataset.nama;
        document.getElementById('operatorName').textContent = state.operatorName;
        document.getElementById('operatorModal').classList.add('hidden');

        // Auto fill penerima dgn operator login
        document.getElementById('penerimaInput').value = state.operatorId;
    });

    async function loadDrivers() {
        const res = await fetch(`/transfer-rak/drivers?q=`);
        const data = await res.json();
        document.getElementById('driverList').innerHTML = data.map(d => `<option value="${d.nama_karyawan}">`).join(
            '');
    }

    // ── KIRIM LOGIC ──
    const checkStartKirim = () => {
        const supir = document.getElementById('supirInput').value.trim();
        const lokasi = document.getElementById('lokasiAsalInput').value;
        const mobil = document.getElementById('mobilKirimInput').value.trim();
        const catatan = document.getElementById('catatanInput').value.trim();
        document.getElementById('btnMulai').disabled = !(supir && lokasi && mobil && state.operatorId);
    };
    document.getElementById('supirInput').addEventListener('input', checkStartKirim);
    document.getElementById('mobilKirimInput').addEventListener('input', checkStartKirim);
    document.getElementById('lokasiAsalInput').addEventListener('change', checkStartKirim);

    document.getElementById('btnMulai').addEventListener('click', async () => {
        try {
            const res = await fetch('/transfer-rak/start', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({
                    id_karyawan: state.operatorId,
                    nama_supir: document.getElementById('supirInput').value.trim(),
                    lokasi_asal: document.getElementById('lokasiAsalInput').value,
                    nama_kendaraan: document.getElementById('mobilKirimInput').value.trim(),
                    catatan: document.getElementById('catatanInput').value.trim()
                })
            });
            const data = await res.json();
            if (!data.success) throw new Error(data.error);

            state.transferId = data.transfer_id;
            document.getElementById('setupKirim').style.display = 'none';
            document.getElementById('footerKirim').style.display = 'none';
            document.getElementById('scanAreaKirim').style.display = 'flex';
            document.getElementById('scanInput').focus();
            showToast('Kirim dimulai, silakan scan rak', 'success');
        } catch (e) {
            showToast(e.message);
        }
    });

    document.getElementById('scanInput').addEventListener('keypress', async (e) => {
        if (e.key !== 'Enter') return;
        const kode = e.target.value.trim();
        e.target.value = '';
        if (!kode) return;

        try {
            const res = await fetch('/transfer-rak/scan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({
                    transfer_rak_id: state.transferId,
                    kode_rak: kode
                })
            });
            const data = await res.json();
            if (!data.success && data.duplicate) {
                showToast('Rak sudah discan!');
                return;
            }
            if (!data.success) throw new Error(data.error);

            document.getElementById('beepOk').play().catch(() => {});
            document.getElementById('totalCount').textContent = data.total;
            document.getElementById('btnFinishKirim').disabled = false;

            const div = document.createElement('div');
            div.className = 'scan-item';
            div.innerHTML =
                `<span>${kode}</span> <span style="font-size:10px;color:#94a3b8">${data.waktu_scan}</span>`;
            document.getElementById('scanList').prepend(div);
        } catch (e) {
            showToast(e.message);
        }
    });

    document.getElementById('btnFinishKirim').addEventListener('click', async () => {
        try {
            const res = await fetch('/transfer-rak/finish', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({
                    transfer_rak_id: state.transferId
                })
            });
            const data = await res.json();
            if (!data.success) throw new Error(data.error);
            showToast('Transfer Selesai!', 'success');
            resetKirim();
        } catch (e) {
            showToast(e.message);
        }
    });

    document.getElementById('btnCancelKirim').addEventListener('click', async () => {
        if (!confirm('Batal?')) return;
        if (state.transferId) {
            await fetch('/transfer-rak/cancel', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({
                    transfer_rak_id: state.transferId
                })
            });
        }
        resetKirim();
    });

    function resetKirim() {
        state.transferId = null;
        document.getElementById('setupKirim').style.display = 'block';
        document.getElementById('footerKirim').style.display = 'block';
        document.getElementById('scanAreaKirim').style.display = 'none';
        document.getElementById('scanList').innerHTML = '';
        document.getElementById('totalCount').textContent = '0';
        document.getElementById('mobilKirimInput').value = '';
        checkStartKirim();
    }

    // ── TERIMA LOGIC ──
    document.getElementById('mobilTerimaInput').addEventListener('keypress', async (e) => {
        if (e.key !== 'Enter') return;
        const mobil = e.target.value.trim();
        if (!mobil) return;

        try {
            const res = await fetch('/transfer-rak/scan-mobil-penerima', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({
                    nama_kendaraan: mobil
                })
            });
            const data = await res.json();
            if (!data.success) throw new Error(data.error);

            state.terimaTransferId = data.transfer.id;
            document.getElementById('tdPengirim').textContent = data.transfer.pengirim;
            document.getElementById('tdSupir').textContent = data.transfer.supir;
            document.getElementById('tdAsal').textContent = data.transfer.lokasi_asal;
            document.getElementById('tdTotal').textContent = data.transfer.total_rak + ' Rak';
            document.getElementById('tdWaktu').textContent = data.transfer.waktu_mulai;
            document.getElementById('tdCatatan').textContent = data.transfer.catatan || '-';

            document.getElementById('terimaDetail').classList.add('visible');
            checkTerimaReady();
        } catch (e) {
            showToast(e.message);
            document.getElementById('terimaDetail').classList.remove('visible');
        }
    });

    const checkTerimaReady = () => {
        const loc = document.getElementById('lokasiTujuanInput').value;
        const pen = document.getElementById('penerimaInput').value;
        document.getElementById('btnProsesTerima').disabled = !(loc && pen && state.terimaTransferId);
    };
    document.getElementById('lokasiTujuanInput').addEventListener('change', checkTerimaReady);
    document.getElementById('penerimaInput').addEventListener('change', checkTerimaReady);

    document.getElementById('btnProsesTerima').addEventListener('click', async () => {
        try {
            const res = await fetch('/transfer-rak/terima', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF
                },
                body: JSON.stringify({
                    transfer_rak_id: state.terimaTransferId,
                    lokasi_tujuan: document.getElementById('lokasiTujuanInput').value,
                    id_karyawan_penerima: document.getElementById('penerimaInput').value
                })
            });
            const data = await res.json();
            if (!data.success) throw new Error(data.error);

            showToast('Penerimaan Berhasil!', 'success');
            document.getElementById('terimaDetail').classList.remove('visible');
            document.getElementById('mobilTerimaInput').value = '';
            state.terimaTransferId = null;
        } catch (e) {
            showToast(e.message);
        }
    });
</script>
