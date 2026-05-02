@extends('MonitoringTransferRak.app')

@section('title', 'Transfer & Konfirmasi Rak (All-in-One)')

@push('styles')
    <style>
        body,
        html {
            background: #0b1220;
            color: #e2e8f0;
            font-family: sans-serif;
        }

        .app-wrap {
            padding: 24px;
            max-width: 600px;
            margin: 24px auto;
        }

        h2 {
            font-size: 20px;
            margin-bottom: 16px;
            color: #64c8ff;
        }

        .section {
            background: rgba(15, 23, 42, 0.85);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px #2226;
        }

        label {
            display: block;
            margin-bottom: 4px;
            color: #94a3b8;
        }

        input,
        select,
        button {
            padding: 10px 12px;
            border-radius: 7px;
            font-size: 15px;
            margin-bottom: 10px;
        }

        input,
        select {
            width: 100%;
            border: 1px solid #60a5fa;
            background: #132034;
            /* Background gelap */
            color: #fff;
            /* Tulisan putih */
        }

        input::placeholder {
            color: #aaa;
            opacity: 1;
        }

        button {
            cursor: pointer;
            font-weight: bold;
        }

        button:disabled {
            opacity: .45;
            cursor: not-allowed;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 11px;
            font-size: 12px;
            margin-left: 10px;
        }

        .status-success {
            background: #22c55e;
            color: white;
        }

        .status-wait {
            background: #fbbf24;
            color: #222;
        }

        .rak-list {
            background: #172036;
            padding: 10px 8px;
            border-radius: 8px;
            margin-bottom: 7px;
        }

        .rak-item {
            color: #64c8ff;
        }

        hr {
            border: 0;
            border-top: 1px solid #1e293b;
            margin: 18px 0;
        }

        #toast {
            position: fixed;
            bottom: 25px;
            left: 50%;
            transform: translateX(-50%);
            background: #333;
            padding: 13px 24px;
            border-radius: 8px;
            color: #fff;
            z-index: 300;
            font-size: 16px;
            min-width: 200px;
            text-align: center;
        }
    </style>
@endpush

<div class="app-wrap">

    {{-- === BAGIAN 1: MULAI / TRANSFER RAK === --}}
    <div class="section" id="sectionMulaiTransfer">
        <h2>Transfer Rak</h2>
        <label>Operator</label>
        <select id="operatorSelect">
            <option value="">-- Pilih Operator --</option>
            @foreach ($karyawan as $k)
                <option value="{{ $k->id }}">{{ $k->name }}</option>
            @endforeach
        </select>
        <label>Nama Supir</label>
        <input list="driverList" id="supirInput" autocomplete="off" placeholder="Ketik/pilih supir...">
        <datalist id="driverList"></datalist>
        <label>Scan Barcode Mobil</label>
        <input type="text" id="mobilInput" autocomplete="off" placeholder="Scan barcode kendaraan...">
        <button id="btnMulai" disabled>Mulai Transfer</button>
        <div id="hintMulai" style="font-size:13px;color:#64748b;margin-top:5px;">Isi semua field sebelum mulai</div>
    </div>

    {{-- === BAGIAN 2: SCAN RAK === --}}
    <div class="section" id="sectionScanRak" style="display:none;">
        <h2>🔍 Scan Rak</h2>
        <div><b>Supir:</b> <span id="supirDisplay"></span> <b>| Mobil:</b> <span id="mobilDisplay"></span></div>
        <hr>
        <input type="text" id="scanInput" placeholder="Scan barcode rak..." autocomplete="off">
        <div>Total rak discan: <b id="scanCount">0</b></div>
        <div class="rak-list" id="listRak"></div>
        <button id="btnFinish" disabled>✔ Selesai Transfer</button>
        <button id="btnCancel" style="background:#f87171;color:white;margin-top:6px">✕ Batal</button>
    </div>

    {{-- === BAGIAN 3: KONFIRMASI PENERIMAAN (SCAN MOBIL) === --}}
    <div class="section">
        <h2>✅ Konfirmasi Penerimaan Rak</h2>
        <input type="text" id="scanMobilPenerima" placeholder="Scan barcode mobil pengirim...">
        <div id="hasilKonfirmasi"></div>
    </div>

</div>

<script>
    const CSRF = '{{ csrf_token() }}';
    const karyawan = @json($karyawan);

    let state = {
        operatorId: null,
        transferId: null,
        supir: '',
        mobil: '',
        rak: [],
    };

    // === DRIVERS AUTOCOMPLETE
    async function loadDrivers(q = '') {
        const res = await fetch(`/transfer-rak/drivers?q=${encodeURIComponent(q)}`);
        const data = await res.json();
        document.getElementById('driverList').innerHTML = data.map(d => `<option value="${d.nama_karyawan}">`).join(
            '');
    }
    loadDrivers();
    document.getElementById('supirInput').addEventListener('input', e => loadDrivers(e.target.value));

    // === ENABLE/DISABLE "Mulai Transfer"
    function checkEnableMulai() {
        const op = document.getElementById('operatorSelect').value;
        const s = document.getElementById('supirInput').value.trim();
        const m = document.getElementById('mobilInput').value.trim();
        const btn = document.getElementById('btnMulai');
        btn.disabled = !(op && s && m);
        if (op && s && m) {
            document.getElementById('hintMulai').innerText = "Siap! Tekan untuk mulai transfer.";
        } else {
            document.getElementById('hintMulai').innerText = "Isi semua field sebelum mulai";
        }
    }
    ['operatorSelect', 'supirInput', 'mobilInput'].forEach(id => document.getElementById(id).addEventListener('input',
        checkEnableMulai));

    // === MULAI TRANSFER ===
    document.getElementById('btnMulai').addEventListener('click', async () => {
        const op = document.getElementById('operatorSelect').value;
        const s = document.getElementById('supirInput').value.trim();
        const m = document.getElementById('mobilInput').value.trim();
        if (!(op && s && m)) return showToast("Mohon lengkapi data!");

        document.getElementById('btnMulai').disabled = true;
        const res = await fetch('/transfer-rak/start', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF
            },
            body: JSON.stringify({
                id_karyawan: op,
                nama_supir: s,
                nama_kendaraan: m
            })
        });
        const data = await res.json();
        if (!data.success) {
            showToast(data.error ?? 'Gagal memulai transfer');
            document.getElementById('btnMulai').disabled = false;
            return;
        }
        state = {
            operatorId: op,
            transferId: data.transfer_id,
            supir: s,
            mobil: m,
            rak: []
        };
        document.getElementById('sectionMulaiTransfer').style.display = 'none';
        document.getElementById('sectionScanRak').style.display = '';
        document.getElementById('operatorSelect').value = state.operatorId; // <--- tetap tampilkan operator
        document.getElementById('supirDisplay').innerText = s;
        document.getElementById('mobilDisplay').innerText = m;
        document.getElementById('scanInput').focus();
        showToast('✓ Transfer dimulai', 'success');
    });

    // === SCAN RAK ===
    document.getElementById('scanInput').addEventListener('keypress', async function(e) {
        if (e.key !== 'Enter') return;
        const kode = this.value.trim();
        this.value = '';
        if (!kode) return;

        if (!state.transferId) return showToast("Transfer belum dimulai!");

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
        if (data.duplicate) {
            showToast('Rak sudah pernah discan!');
            return;
        }
        if (!data.success) {
            showToast(data.error ?? 'Scan gagal');
            return;
        }

        state.rak.push({
            kode,
            kapan: data.waktu_scan
        });
        document.getElementById('scanCount').innerText = state.rak.length;
        document.getElementById('btnFinish').disabled = false;
        tampilkanRak();
    });

    function tampilkanRak() {
        let html = state.rak.slice().reverse().map((d, i) =>
            `<div class="rak-item">${i + 1}. <b>${d.kode}</b> <span style="color:#64748b;font-size:12px;">${d.kapan}</span></div>`
        ).join('');
        document.getElementById('listRak').innerHTML = html || "<em>Belum ada scan rak.</em>";
    }

    // === SELESAI TRANSFER ===
    document.getElementById('btnFinish').addEventListener('click', async () => {
        if (!state.transferId) return;
        document.getElementById('btnFinish').disabled = true;
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
        if (!data.success) {
            showToast(data.error ?? 'Gagal selesai transfer');
            document.getElementById('btnFinish').disabled = false;
            return;
        }
        showToast(`✓ Selesai! ${data.total} rak tersimpan`, 'success');
        resetForm();
    });

    // === BATAL ===
    document.getElementById('btnCancel').addEventListener('click', async () => {
        if (!confirm('Batalkan transfer ini?')) return;
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
        showToast("Transfer dibatalkan.");
        resetForm();
    });

    // === RESET ===
    function resetForm() {
        document.getElementById('sectionMulaiTransfer').style.display = '';
        document.getElementById('sectionScanRak').style.display = 'none';
        document.getElementById('supirInput').value = '';
        document.getElementById('mobilInput').value = '';
        document.getElementById('hintMulai').innerText = "Isi semua field sebelum mulai";

        // OPERATOR TETAP TIDAK DI-RESET, hanya jika diubah manual!
        state = {
            operatorId: state.operatorId,
            transferId: null,
            supir: '',
            mobil: '',
            rak: []
        };
        document.getElementById('operatorSelect').value = state.operatorId;
        document.getElementById('scanCount').innerText = '0';
        tampilkanRak();
        document.getElementById('btnMulai').disabled = !state.operatorId;
    }

    // === KONFIRMASI PENERIMAAN by SCAN MOBIL ===
    document.getElementById('scanMobilPenerima').addEventListener('keypress', async function(e) {
        if (e.key !== 'Enter') return;
        const kodeMobil = this.value.trim();
        this.value = '';
        if (!kodeMobil) return;
        // Kirim ke backend endpoint konfirmasi
        const res = await fetch('/transfer-rak/confirm-by-mobil', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF
            },
            body: JSON.stringify({
                barcode_mobil: kodeMobil
            })
        });
        const data = await res.json();
        if (data.success) {
            showToast("Transfer berhasil dikonfirmasi!", "success");
            document.getElementById('hasilKonfirmasi').innerHTML =
                `<div class="status-badge status-success">Transfer ID #${data.transfer_id} berhasil dikonfirmasi.<br>Rak: ${data.total_rak ?? '-'} | Supir: ${data.supir ?? '-'} </div>`;
        } else {
            showToast(data.error ?? "Transfer tidak ditemukan atau sudah selesai.", "error");
            document.getElementById('hasilKonfirmasi').innerHTML =
                `<div class="status-badge status-wait">${data.error ?? 'Transfer tidak ditemukan atau sudah selesai.'}</div>`;
        }
    });

    // === TOAST ===
    function showToast(msg, type = 'error') {
        let el = document.createElement('div');
        el.id = 'toast';
        el.style.background = (type === 'success' ? '#22c55e' : '#f87171');
        el.innerText = msg;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 2800);
    }
</script>
