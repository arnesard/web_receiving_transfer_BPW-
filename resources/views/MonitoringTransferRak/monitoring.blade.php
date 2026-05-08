@extends('MonitoringTransferRak.app')

@section('content')
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
                height: 100dvh;
                display: flex;
                flex-direction: column;
                overflow: hidden;
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
                overflow-y: auto;
            }

            .tab-content {
                -webkit-overflow-scrolling: touch;
                height: 100%;
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

            /* Fixed scrolling for Terima tab */
            .setup-panel {
                max-height: calc(100vh - 150px);
                overflow-y: auto;
                padding-bottom: 30px;
            }

            .scan-list {
                max-height: 200px;
                overflow-y: auto;
                border: 1px solid rgba(255, 255, 255, 0.05);
                border-radius: 8px;
                margin-bottom: 15px;
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

            #contentKosong {
                padding-bottom: 100px;
            }

            .scroll-area {
                flex: 1;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
            }

            .mulai-footer {
                position: sticky;
                bottom: 0;
                z-index: 10;
            }
        </style>
    @endpush

    <div class="app-wrap">
        <div class="header">
            <div class="header-top">
                <div class="app-title">📦 Transfer Rak</div>
                <div class="operator-badge" onclick="openOperatorModal()">
                    👤 <span id="operatorName">Pilih Operator</span> ✏️
                </div>
            </div>
            <div class="tab-container">
                <div class="tab-btn active" id="tabKirim" onclick="switchTab('kirim')" style="font-size:11px">📤 KIRIM
                </div>
                <div class="tab-btn" id="tabTerima" onclick="switchTab('terima')" style="font-size:11px">📥 TERIMA</div>
                <div class="tab-btn" id="tabKosong" onclick="switchTab('kosong')" style="font-size:11px">RAK KOSONG</div>
            </div>
        </div>

        {{-- TAB KIRIM --}}
        <div class="tab-content active" id="contentKirim">
            <div class="scroll-area">
                <div class="setup-panel" id="setupKirim">
                    <div class="setup-row">
                        <label class="setup-label">🚗 Nama Supir</label>

                        <input list="driverList" class="setup-input" id="supirInput" placeholder="Ketik / pilih supir..."
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
                        <input type="text" id="scanInput" placeholder="Arahkan scanner ke barcode rak..."
                            autocomplete="off">
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
        </div>

        {{-- TAB TERIMA --}}
        <div class="tab-content" id="contentTerima">
            <div class="scroll-area">
                <div class="setup-panel" id="setupTerima">
                    <div class="setup-row">
                        <label class="setup-label">🚙 Scan Barcode Kendaraan Datang</label>
                        <input type="text" class="setup-input" id="mobilTerimaInput" placeholder="Scan kendaraan...">
                    </div>

                    <div class="terima-detail" id="terimaDetail">
                        <div class="td-row"><span class="td-label">Pengirim:</span> <span class="td-val"
                                id="tdPengirim">-</span></div>
                        <div class="td-row"><span class="td-label">Supir:</span> <span class="td-val"
                                id="tdSupir">-</span></div>
                        <div class="td-row"><span class="td-label">Asal:</span> <span class="td-val"
                                id="tdAsal">-</span>
                        </div>
                        <div class="td-row"><span class="td-label">Total Rak:</span> <span class="td-val"
                                id="tdTotal">-</span></div>
                        <div class="td-row"><span class="td-label">Sudah Diterima:</span> <span class="td-val"
                                id="tdSudahDiterima" style="color:#4ade80">-</span></div>
                        <div class="td-row"><span class="td-label">Sisa:</span> <span class="td-val" id="tdSisa"
                                style="color:#f59e0b">-</span></div>
                        <div class="td-row"><span class="td-label">Waktu:</span> <span class="td-val"
                                id="tdWaktu">-</span></div>
                        <div class="td-row"><span class="td-label">Catatan:</span> <span class="td-val"
                                id="tdCatatan">-</span></div>

                        <div class="td-row" style="flex-direction:column; align-items:flex-start; margin-top:5px;">
                            <span class="td-label" style="margin-bottom:5px;">📦 Daftar Rak di Mobil:</span>
                            <div id="tdListRakMobil" style="display:flex; flex-wrap:wrap; gap:6px; width:100%;">
                                <span style="color:#475569; font-size:11px;">(Scan kendaraan untuk melihat isi)</span>
                            </div>
                        </div>

                        <hr style="border:0; border-top:1px solid rgba(255,255,255,0.1); margin:10px 0;">

                        {{-- Scan rak untuk diterima --}}
                        <div class="setup-row">
                            <label class="setup-label">🔍 Scan Rak yang Diturunkan</label>
                            <div style="display:flex; gap:8px;">
                                <input type="text" class="setup-input" id="scanTerimaInput"
                                    placeholder="Scan barcode rak..." autocomplete="off">
                                <button class="btn-start" id="btnTerimaSemua"
                                    style="margin-top:0; width:auto; padding:0 15px; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); font-size:12px;">TERIMA
                                    SEMUA</button>
                            </div>
                        </div>
                        <div class="counter-row" style="margin-bottom:8px">
                            <div class="counter-box">
                                <div class="counter-label">Rak Discan</div>
                                <div class="counter-value" id="terimaCount" style="color:#4ade80">0</div>
                            </div>
                        </div>
                        <div class="scan-list" id="terimaScanList"
                            style="max-height:150px;overflow-y:auto;margin-bottom:8px"></div>

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

                        <button class="btn-start" id="btnProsesTerima"
                            style="margin-top:10px;background:linear-gradient(135deg,#22c55e,#16a34a)" disabled>SELESAIKAN
                            PENERIMAAN</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB RAK/PALET KOSONG --}}
        <div class="tab-content" id="contentKosong">
            <div class="scroll-area">
                <div class="setup-panel" id="setupKosong">
                    <div class="setup-row">
                        <label class="setup-label">🚗 Nama Supir</label>

                        <input list="driverListKosong" class="setup-input" id="supirKosongInput"
                            placeholder="Ketik / pilih supir..." autocomplete="off">

                        <datalist id="driverListKosong"></datalist>
                    </div>
                    <div class="setup-row">
                        <label class="setup-label">🏢 Lokasi Asal</label>
                        <select class="setup-select" id="lokasiAsalKosongInput">
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
                    </div>
                    <div class="setup-row">
                        <label class="setup-label">🚙 Scan Barcode Kendaraan</label>
                        <input type="text" class="setup-input" id="mobilKosongInput"
                            placeholder="Arahkan scanner ke kendaraan...">
                    </div>
                    <div class="setup-row">
                        <label class="setup-label">📦 Jumlah Rak Kosong</label>
                        <input type="number" class="setup-input" id="jmlRakKosongInput" min="0" value="-"
                            placeholder="Masukkan jumlah rak kosong...">
                    </div>
                    <div class="setup-row">
                        <label class="setup-label">📦 Jumlah Palet Kosong</label>
                        <input type="number" class="setup-input" id="jmlPaletKosongInput" min="0"
                            value="-" placeholder="Masukkan jumlah palet kosong...">
                    </div>
                    <div class="setup-row">
                        <label class="setup-label">📝 Catatan (Opsional)</label>
                        <textarea class="setup-input" id="catatanKosongInput" rows="2" placeholder="Tambahkan catatan jika perlu..."></textarea>
                    </div>
                </div>

                <div class="mulai-footer" id="footerKosong">
                    <button class="btn-start" id="btnKirimKosong" disabled
                        style="background:linear-gradient(135deg,#f59e0b,#d97706)">📦 KIRIM RAK/PALET KOSONG</button>
                </div>
            </div>
        </div>

        {{-- MODAL KONFIRMASI RAK KOSONG --}}
        <div class="modal-overlay hidden" id="konfirmasiKosongModal">
            <div class="modal-box">
                <div class="modal-title">📦 Konfirmasi Kirim Rak/Palet Kosong</div>
                <div id="konfirmasiKosongBody" style="margin:14px 0;font-size:13px;color:#cbd5e1;line-height:1.8"></div>
                <div style="display:flex;gap:10px">
                    <button class="btn-center btn-cancel"
                        onclick="document.getElementById('konfirmasiKosongModal').classList.add('hidden')">Batal</button>
                    <button class="btn-center btn-finish" id="btnKonfirmasiKosong">Konfirmasi</button>
                </div>
            </div>
        </div>
    </div>


    {{-- MODAL PILIH OPERATOR --}}
    <div class="modal-overlay" id="operatorModal">
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
            document.getElementById('tabKosong').classList.toggle('active', tab === 'kosong');
            document.getElementById('contentKirim').classList.toggle('active', tab === 'kirim');
            document.getElementById('contentTerima').classList.toggle('active', tab === 'terima');
            document.getElementById('contentKosong').classList.toggle('active', tab === 'kosong');
        }

        function showToast(msg, type = 'error') {
            const t = document.createElement('div');
            t.className = `toast ${type}`;
            t.textContent = msg;
            document.body.appendChild(t);
            setTimeout(() => t.remove(), 3000);
        }

        // ── INIT ──

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
                state.scanList = [];

                // UI Switch
                document.getElementById('setupKirim').style.display = 'none';
                document.getElementById('footerKirim').style.display = 'none';
                document.getElementById('scanAreaKirim').style.display = 'flex';
                document.getElementById('scanInput').focus();

                if (data.joined) {
                    document.getElementById('totalCount').textContent = data.total_sudah;
                    showToast(`Bergabung ke transfer LB1. Sudah ada ${data.total_sudah} rak.`, 'success');
                } else {
                    document.getElementById('totalCount').textContent = '0';
                    showToast('Kirim dimulai, silakan scan rak', 'success');
                }
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
                        kode_rak: kode,
                        id_karyawan: state.operatorId // Kirim ID operator yg sedang login
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
        state.terimaScanList = []; // list kode_rak yang di-scan untuk diterima

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
                state.terimaTipe = data.transfer.tipe; // Simpan tipenya
                state.terimaScanList = [];
                document.getElementById('terimaScanList').innerHTML = '';
                document.getElementById('terimaCount').textContent = '0';

                document.getElementById('tdPengirim').textContent = data.transfer.pengirim;
                document.getElementById('tdSupir').textContent = data.transfer.supir;
                document.getElementById('tdAsal').textContent = data.transfer.lokasi_asal;

                // Info sesuai tipe
                if (data.transfer.tipe === 'rak_kosong') {
                    let kosongInfo = [];
                    if (data.transfer.jumlah_rak_kosong > 0) kosongInfo.push(data.transfer.jumlah_rak_kosong +
                        ' Rak Kosong');
                    if (data.transfer.jumlah_palet_kosong > 0) kosongInfo.push(data.transfer
                        .jumlah_palet_kosong + ' Palet Kosong');
                    document.getElementById('tdTotal').textContent = kosongInfo.join(', ') || '-';
                    document.getElementById('tdSudahDiterima').textContent = '-';
                    document.getElementById('tdSisa').textContent = '-';
                    // Sembunyikan scan rak untuk rak kosong
                    document.getElementById('scanTerimaInput').parentElement.parentElement.style.display =
                        'none';
                    document.querySelector('#terimaCount').parentElement.parentElement.style.display = 'none';
                    document.getElementById('tdListRakMobil').parentElement.style.display = 'none';
                } else {
                    document.getElementById('tdTotal').textContent = data.transfer.total_rak + ' Rak';
                    document.getElementById('tdSudahDiterima').textContent = data.transfer.sudah_diterima +
                        ' Rak';
                    document.getElementById('tdSisa').textContent = data.transfer.sisa_rak + ' Rak';
                    document.getElementById('scanTerimaInput').parentElement.parentElement.style.display =
                        'flex';
                    document.querySelector('#terimaCount').parentElement.parentElement.style.display = 'flex';
                    document.getElementById('tdListRakMobil').parentElement.style.display = 'flex';
                }

                document.getElementById('tdWaktu').textContent = data.transfer.waktu_mulai;
                document.getElementById('tdCatatan').textContent = data.transfer.catatan || '-';

                // Tampilkan daftar rak di mobil secara visual
                const listRakMobil = document.getElementById('tdListRakMobil');
                listRakMobil.innerHTML = '';
                if (data.transfer.tipe !== 'rak_kosong' && data.transfer.belum_diterima && data.transfer
                    .belum_diterima.length > 0) {
                    data.transfer.belum_diterima.forEach(r => {
                        const badge = document.createElement('span');
                        badge.style =
                            'background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); padding:4px 8px; border-radius:6px; font-size:11px; color:#cbd5e1;';
                        badge.textContent = r.kode_rak;
                        listRakMobil.appendChild(badge);
                    });
                } else if (data.transfer.tipe !== 'rak_kosong') {
                    listRakMobil.innerHTML =
                        '<span style="color:#475569; font-size:11px;">(Tidak ada rak)</span>';
                }

                document.getElementById('terimaDetail').classList.add('visible');
                if (data.transfer.tipe !== 'rak_kosong') {
                    document.getElementById('scanTerimaInput').focus();
                }

                // Simpan list rak yang belum diterima untuk fitur "Terima Semua"
                state.rakBelumDiterima = data.transfer.belum_diterima ? data.transfer.belum_diterima.map(r => r
                    .kode_rak) : [];

                checkTerimaReady();
            } catch (e) {
                showToast(e.message);
                document.getElementById('terimaDetail').classList.remove('visible');
            }
        });

        // Fungsi Helper: Render badge rak di mobil yang belum dipindah ke list siap terima
        const renderListRakMobil = () => {
            const listRakMobil = document.getElementById('tdListRakMobil');
            listRakMobil.innerHTML = '';

            // Rak di mobil = Rak Belum Diterima MINUS Rak yang sudah masuk list scan
            const sisaDiMobil = (state.rakBelumDiterima || []).filter(kode => !state.terimaScanList.includes(kode));

            if (sisaDiMobil.length > 0) {
                sisaDiMobil.forEach(kode => {
                    const badge = document.createElement('span');
                    badge.style =
                        'background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); padding:4px 8px; border-radius:6px; font-size:11px; color:#cbd5e1;';
                    badge.textContent = kode;
                    listRakMobil.appendChild(badge);
                });
            } else {
                listRakMobil.innerHTML =
                    '<span style="color:#475569; font-size:11px;">(Semua rak sudah masuk list siap terima)</span>';
            }
        };

        // Tombol Terima Semua
        document.getElementById('btnTerimaSemua').addEventListener('click', () => {
            if (!state.rakBelumDiterima || state.rakBelumDiterima.length === 0) return;

            state.terimaScanList = [...state.rakBelumDiterima];
            renderTerimaScanList(); // Render ulang list scan
            renderListRakMobil(); // Update list di mobil (bakal kosong)

            showToast('Semua rak ditambahkan ke list!', 'success');
            checkTerimaReady();
        });

        // Fungsi Helper: Render list scan siap terima
        const renderTerimaScanList = () => {
            document.getElementById('terimaCount').textContent = state.terimaScanList.length;
            const listDiv = document.getElementById('terimaScanList');
            listDiv.innerHTML = '';

            state.terimaScanList.forEach(kode => {
                const div = document.createElement('div');
                div.className = 'scan-item';
                div.innerHTML = `
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="color:#4ade80">✓</span>
                    <span style="color:#cbd5e1">${kode}</span>
                </div>
                <button class="btn-batal-scan" data-kode="${kode}" style="background:rgba(239,68,68,0.1); color:#ef4444; border:1px solid rgba(239,68,68,0.2); padding:2px 8px; border-radius:4px; font-size:10px; cursor:pointer;">BATAL</button>
            `;
                listDiv.prepend(div);
            });

            // Event listener buat tombol batal
            listDiv.querySelectorAll('.btn-batal-scan').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const kode = e.target.dataset.kode;
                    state.terimaScanList = state.terimaScanList.filter(k => k !== kode);
                    renderTerimaScanList();
                    renderListRakMobil();
                    checkTerimaReady();
                });
            });
        };

        // Scan rak saat penerimaan
        document.getElementById('scanTerimaInput').addEventListener('keypress', async (e) => {
            if (e.key !== 'Enter') return;
            const kode = e.target.value.trim();
            e.target.value = '';
            if (!kode || !state.terimaTransferId) return;

            if (state.terimaScanList.includes(kode)) {
                showToast('Rak sudah ada di list!');
                return;
            }

            try {
                const res = await fetch('/transfer-rak/scan-terima', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify({
                        transfer_rak_id: state.terimaTransferId,
                        kode_rak: kode
                    })
                });
                const data = await res.json();
                if (!data.success) {
                    showToast(data.error);
                    return;
                }

                document.getElementById('beepOk').play().catch(() => {});
                state.terimaScanList.push(kode);

                renderTerimaScanList();
                renderListRakMobil();
                checkTerimaReady();
            } catch (e) {
                showToast(e.message);
            }
        });

        const checkTerimaReady = () => {
            const loc = document.getElementById('lokasiTujuanInput').value;
            const pen = document.getElementById('penerimaInput').value;

            if (state.terimaTipe === 'rak_kosong') {
                // Kalo rak kosong, gak perlu nunggu scan
                document.getElementById('btnProsesTerima').disabled = !(loc && pen && state.terimaTransferId);
            } else {
                // Kalo rak isi, harus ada yang di-scan
                const hasScan = state.terimaScanList.length > 0;
                document.getElementById('btnProsesTerima').disabled = !(loc && pen && state.terimaTransferId &&
                    hasScan);
            }
        };
        document.getElementById('lokasiTujuanInput').addEventListener('change', checkTerimaReady);
        document.getElementById('penerimaInput').addEventListener('change', checkTerimaReady);

        document.getElementById('btnProsesTerima').addEventListener('click', async () => {
            // confirm() dihapus biar langsung jalan
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
                        id_karyawan_penerima: document.getElementById('penerimaInput').value,
                        kode_rak_list: state.terimaScanList
                    })
                });
                const data = await res.json();
                if (!data.success) throw new Error(data.error);

                showToast(data.message, 'success');

                if (data.fully_received) {
                    // Reset semua
                    document.getElementById('terimaDetail').classList.remove('visible');
                    document.getElementById('mobilTerimaInput').value = '';
                    state.terimaTransferId = null;
                } else {
                    // Update progress, reset scan list
                    document.getElementById('tdSudahDiterima').textContent = data.total_diterima + ' Rak';
                    document.getElementById('tdSisa').textContent = data.sisa + ' Rak';
                }
                state.terimaScanList = [];
                document.getElementById('terimaScanList').innerHTML = '';
                document.getElementById('terimaCount').textContent = '0';
                checkTerimaReady();
            } catch (e) {
                showToast(e.message);
            }
        });
        // ── RAK/PALET KOSONG LOGIC ──
        const checkKosongReady = () => {
            const supir = document.getElementById('supirKosongInput').value.trim();
            const lokasi = document.getElementById('lokasiAsalKosongInput').value;
            const mobil = document.getElementById('mobilKosongInput').value.trim();
            const jmlRak = parseInt(document.getElementById('jmlRakKosongInput').value) || 0;
            const jmlPalet = parseInt(document.getElementById('jmlPaletKosongInput').value) || 0;
            document.getElementById('btnKirimKosong').disabled = !(supir && lokasi && mobil && state.operatorId && (
                jmlRak > 0 || jmlPalet > 0));
        };
        document.getElementById('supirKosongInput').addEventListener('input', checkKosongReady);
        document.getElementById('lokasiAsalKosongInput').addEventListener('change', checkKosongReady);
        document.getElementById('mobilKosongInput').addEventListener('input', checkKosongReady);
        document.getElementById('jmlRakKosongInput').addEventListener('input', checkKosongReady);
        document.getElementById('jmlPaletKosongInput').addEventListener('input', checkKosongReady);

        document.getElementById('btnKirimKosong').addEventListener('click', () => {
            const supir = document.getElementById('supirKosongInput').value.trim();
            const lokasi = document.getElementById('lokasiAsalKosongInput').value;
            const mobil = document.getElementById('mobilKosongInput').value.trim();
            const jmlRak = parseInt(document.getElementById('jmlRakKosongInput').value) || 0;
            const jmlPalet = parseInt(document.getElementById('jmlPaletKosongInput').value) || 0;
            const catatan = document.getElementById('catatanKosongInput').value.trim();

            document.getElementById('konfirmasiKosongBody').innerHTML = `
            <div style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.25);border-radius:10px;padding:12px">
                <div style="margin-bottom:6px"><strong>Operator:</strong> ${state.operatorName}</div>
                <div style="margin-bottom:6px"><strong>Supir:</strong> ${supir}</div>
                <div style="margin-bottom:6px"><strong>Asal:</strong> ${lokasi}</div>
                <div style="margin-bottom:6px"><strong>Kendaraan:</strong> ${mobil}</div>
                <div style="margin-bottom:6px"><strong>Rak Kosong:</strong> <span style="color:#f59e0b;font-weight:700">${jmlRak}</span></div>
                <div style="margin-bottom:6px"><strong>Palet Kosong:</strong> <span style="color:#f59e0b;font-weight:700">${jmlPalet}</span></div>
                ${catatan ? `<div><strong>Catatan:</strong> ${catatan}</div>` : ''}
            </div>
        `;
            document.getElementById('konfirmasiKosongModal').classList.remove('hidden');
        });

        document.getElementById('btnKonfirmasiKosong').addEventListener('click', async () => {
            try {
                const res = await fetch('/transfer-rak/start-kosong', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF
                    },
                    body: JSON.stringify({
                        id_karyawan: state.operatorId,
                        nama_supir: document.getElementById('supirKosongInput').value.trim(),
                        lokasi_asal: document.getElementById('lokasiAsalKosongInput').value,
                        nama_kendaraan: document.getElementById('mobilKosongInput').value
                            .trim(),
                        jumlah_rak_kosong: parseInt(document.getElementById('jmlRakKosongInput')
                            .value) || 0,
                        jumlah_palet_kosong: parseInt(document.getElementById(
                            'jmlPaletKosongInput').value) || 0,
                        catatan: document.getElementById('catatanKosongInput').value.trim()
                    })
                });
                const data = await res.json();
                if (!data.success) throw new Error(data.error);

                document.getElementById('konfirmasiKosongModal').classList.add('hidden');
                showToast('Rak/Palet kosong berhasil dikirim!', 'success');
                resetKosong();
            } catch (e) {
                showToast(e.message);
            }
        });

        function resetKosong() {
            document.getElementById('supirKosongInput').value = '';
            document.getElementById('lokasiAsalKosongInput').value = '';
            document.getElementById('mobilKosongInput').value = '';
            document.getElementById('jmlRakKosongInput').value = '0';
            document.getElementById('jmlPaletKosongInput').value = '0';
            document.getElementById('catatanKosongInput').value = '';
            checkKosongReady();
        }

        document.addEventListener('DOMContentLoaded', function() {

            const driverList = document.getElementById('driverList');
            const driverListKosong = document.getElementById('driverListKosong');

            fetch('/transfer-rak/drivers')
                .then(response => response.json())
                .then(data => {

                    data.forEach(driver => {

                        // TAB KIRIM
                        let option1 = document.createElement('option');
                        option1.value = driver.nama_karyawan;
                        driverList.appendChild(option1);

                        // TAB KOSONG
                        let option2 = document.createElement('option');
                        option2.value = driver.nama_karyawan;
                        driverListKosong.appendChild(option2);

                    });

                })
                .catch(error => {
                    console.error(error);
                });

        });
    </script>
@endsection
