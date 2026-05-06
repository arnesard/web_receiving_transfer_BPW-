@extends('MonitoringTransferRak.app')
@section('title', 'Dashboard Transfer Rak')

@push('styles')
    <style>
        /* ── RESET KHUSUS DASHBOARD ── */
        body {
            overflow-y: auto !important;
        }

        .dash-wrap {
            padding: 14px 14px 80px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* ── TOP BAR ── */
        .dash-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 14px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .dash-title {
            font-size: 16px;
            font-weight: 800;
            color: #64c8ff;
            text-shadow: 0 0 10px rgba(100, 200, 255, 0.3);
        }

        .dash-refresh {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.25);
            border-radius: 8px;
            padding: 7px 12px;
            color: #64c8ff;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .dash-refresh:hover {
            background: rgba(59, 130, 246, 0.2);
        }

        .refresh-icon {
            font-size: 14px;
            transition: transform 0.5s;
        }

        .refresh-icon.spin {
            animation: spinOnce 0.5s ease;
        }

        @keyframes spinOnce {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* ── FILTER TABS ── */
        .filter-tabs {
            display: flex;
            gap: 6px;
            margin-bottom: 16px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 10px;
            padding: 4px;
        }

        .filter-tab {
            flex: 1;
            padding: 8px;
            border-radius: 8px;
            border: none;
            background: transparent;
            color: #64748b;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }

        .filter-tab.active {
            background: rgba(59, 130, 246, 0.2);
            color: #64c8ff;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        /* ── KPI GRID ── */
        .kpi-grid {
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
            transition: transform 0.2s;
        }

        .kpi-card:hover {
            transform: translateY(-2px);
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--card-color, #3b82f6);
            opacity: 0.7;
        }

        .kpi-card.wide {
            grid-column: span 2;
        }

        .kpi-icon {
            font-size: 22px;
            margin-bottom: 6px;
            display: block;
        }

        .kpi-label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .kpi-value {
            font-size: 32px;
            font-weight: 900;
            color: var(--card-color, #64c8ff);
            line-height: 1;
            text-shadow: 0 0 20px rgba(100, 200, 255, 0.3);
        }

        .kpi-sub {
            font-size: 10px;
            color: #475569;
            margin-top: 4px;
        }

        /* Warna per card */
        .kpi-blue {
            --card-color: #64c8ff;
        }

        .kpi-green {
            --card-color: #22c55e;
        }

        .kpi-purple {
            --card-color: #a78bfa;
        }

        .kpi-yellow {
            --card-color: #fbbf24;
        }

        .kpi-orange {
            --card-color: #fb923c;
        }

        /* Shimmer loading */
        .kpi-value.loading {
            background: linear-gradient(90deg, rgba(255, 255, 255, 0.05) 25%, rgba(255, 255, 255, 0.1) 50%, rgba(255, 255, 255, 0.05) 75%);
            background-size: 200%;
            animation: shimmer 1.2s infinite;
            border-radius: 6px;
            color: transparent;
            width: 60px;
            height: 36px;
        }

        @keyframes shimmer {
            from {
                background-position: 200% 0;
            }

            to {
                background-position: -200% 0;
            }
        }

        /* ── CHARTS ROW ── */
        .charts-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 16px;
        }

        .chart-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(59, 130, 246, 0.15);
            border-radius: 14px;
            padding: 14px;
        }

        .chart-title {
            font-size: 12px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 12px;
        }

        /* ── ACTIVITY FEED ── */
        .activity-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(59, 130, 246, 0.15);
            border-radius: 14px;
            padding: 14px;
        }

        .activity-title {
            font-size: 12px;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 12px;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-status {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .status-selesai {
            background: #22c55e;
            box-shadow: 0 0 6px rgba(34, 197, 94, 0.6);
        }

        .status-proses {
            background: #fbbf24;
            box-shadow: 0 0 6px rgba(251, 191, 36, 0.6);
            animation: blink 1s ease infinite;
        }

        .status-batal {
            background: #f87171;
            box-shadow: 0 0 6px rgba(248, 113, 113, 0.5);
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.3;
            }
        }

        .activity-main {
            flex: 1;
            min-width: 0;
        }

        .activity-op {
            font-size: 12px;
            font-weight: 600;
            color: #e2e8f0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .activity-sub {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .activity-right {
            text-align: right;
            flex-shrink: 0;
        }

        .activity-rak {
            font-size: 14px;
            font-weight: 800;
            color: #64c8ff;
        }

        .activity-time {
            font-size: 10px;
            color: #475569;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 24px;
            color: #475569;
            font-size: 13px;
        }

        /* ── DESKTOP ── */
        @media (min-width: 768px) {
            .kpi-grid {
                grid-template-columns: repeat(5, 1fr);
            }

            .kpi-card.wide {
                grid-column: span 1;
            }

            .charts-row {
                grid-template-columns: 3fr 2fr;
            }

            .dash-title {
                font-size: 18px;
            }
        }
    </style>
@endpush

@section('content')    
<div class="dash-wrap">
    {{-- HEADER --}}
    <div class="dash-header">
        <div class="dash-title">Dashboard Transfer Rak</div>
        <button class="dash-refresh" id="btnRefresh" onclick="loadData()">
            <span class="refresh-icon" id="refreshIcon">🔄</span>
            <span>Refresh</span>
        </button>
    </div>

    {{-- FILTER --}}
    <div class="filter-tabs">
        <button class="filter-tab active" data-range="today" onclick="setRange('today', this)">Hari Ini</button>
        <button class="filter-tab" data-range="week" onclick="setRange('week', this)">Minggu Ini</button>
        <button class="filter-tab" data-range="month" onclick="setRange('month', this)">Bulan Ini</button>
    </div>

    {{-- KPI CARDS --}}
    <div class="kpi-grid">
        <div class="kpi-card kpi-blue">
            <span class="kpi-icon">📦</span>
            <div class="kpi-label">Total Rak</div>
            <div class="kpi-value loading" id="kpiTotalRak">0</div>
            <div class="kpi-sub">rak dipindahkan</div>
        </div>
        <div class="kpi-card kpi-green">
            <span class="kpi-icon">✅</span>
            <div class="kpi-label">Transfer Selesai</div>
            <div class="kpi-value loading" id="kpiSelesai">0</div>
            <div class="kpi-sub">transaksi sukses</div>
        </div>
        <div class="kpi-card kpi-purple">
            <span class="kpi-icon">🎯</span>
            <div class="kpi-label">Completion Rate</div>
            <div class="kpi-value loading" id="kpiRate">0%</div>
            <div class="kpi-sub">dari total transfer</div>
        </div>
        <div class="kpi-card kpi-yellow">
            <span class="kpi-icon">⏱️</span>
            <div class="kpi-label">Rata-rata Durasi</div>
            <div class="kpi-value loading" id="kpiDurasi">-</div>
            <div class="kpi-sub">per transfer</div>
        </div>
        <div class="kpi-card kpi-orange wide">
            <span class="kpi-icon">🔄</span>
            <div class="kpi-label">Sedang Proses</div>
            <div class="kpi-value loading" id="kpiProses">0</div>
            <div class="kpi-sub">transfer aktif sekarang</div>
        </div>
    </div>

    {{-- CHARTS --}}
    <div class="charts-row">
        <div class="chart-card">
            <div class="chart-title">📈 Trend Rak 7 Hari Terakhir</div>
            <div id="chartTrend"></div>
        </div>
        <div class="chart-card">
            <div class="chart-title">🏆 Top 5 Operator</div>
            <div id="chartOperator"></div>
        </div>
    </div>

    {{-- ACTIVITY FEED --}}
    <div class="activity-card">
        <div class="activity-title">🕐 Aktivitas Terbaru</div>
        <div id="activityFeed">
            <div class="empty-state">Memuat data...</div>
        </div>
    </div>
</div>
@endsection

<script src="{{ asset('js/apexcharts.min.js') }}"></script>
<script>
    let currentRange = 'today';
    let trendChart = null;
    let operatorChart = null;

    // ── FILTER ──
    function setRange(range, el) {
        currentRange = range;
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        el.classList.add('active');
        loadData();
    }

    // ── REFRESH ICON ──
    function spinRefresh() {
        const icon = document.getElementById('refreshIcon');
        icon.classList.remove('spin');
        void icon.offsetWidth;
        icon.classList.add('spin');
    }

    // ── LOAD ALL DATA ──
    async function loadData() {
        spinRefresh();
        setLoadingState();

        try {
            const res = await fetch(`/transfer-rak/dashboard/data?range=${currentRange}`);
            const data = await res.json();

            updateKPI(data.kpi);
            updateTrendChart(data.trend);
            updateOperatorChart(data.top_operators);
            updateActivity(data.activity);
        } catch (e) {
            console.error(e);
        }
    }

    // ── KPI ──
    function setLoadingState() {
        ['kpiTotalRak', 'kpiSelesai', 'kpiRate', 'kpiDurasi', 'kpiProses'].forEach(id => {
            const el = document.getElementById(id);
            el.classList.add('loading');
        });
    }

    function updateKPI(kpi) {
        const set = (id, val) => {
            const el = document.getElementById(id);
            el.classList.remove('loading');
            el.textContent = val;
        };
        set('kpiTotalRak', kpi.total_rak.toLocaleString('id-ID'));
        set('kpiSelesai', kpi.transfer_selesai);
        set('kpiRate', kpi.completion_rate + '%');
        set('kpiDurasi', kpi.avg_durasi);
        set('kpiProses', kpi.sedang_proses);
    }

    // ── TREND CHART ──
    function updateTrendChart(trend) {
        const opts = {
            series: [{
                name: 'Total Rak',
                data: trend.map(t => t.total)
            }],
            chart: {
                type: 'area',
                height: 180,
                background: 'transparent',
                toolbar: {
                    show: false
                },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 600
                },
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 2,
                colors: ['#64c8ff']
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.35,
                    opacityTo: 0.02,
                    stops: [0, 90, 100],
                    colorStops: [{
                        offset: 0,
                        color: '#64c8ff',
                        opacity: 0.35
                    }, {
                        offset: 100,
                        color: '#64c8ff',
                        opacity: 0
                    }]
                }
            },
            xaxis: {
                categories: trend.map(t => t.date),
                labels: {
                    style: {
                        colors: '#64748b',
                        fontSize: '10px'
                    }
                },
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#64748b',
                        fontSize: '10px'
                    }
                },
                min: 0
            },
            grid: {
                borderColor: 'rgba(255,255,255,0.05)',
                strokeDashArray: 4
            },
            tooltip: {
                theme: 'dark'
            },
            theme: {
                mode: 'dark'
            },
        };

        if (trendChart) {
            trendChart.updateSeries([{
                data: trend.map(t => t.total)
            }]);
            trendChart.updateOptions({
                xaxis: {
                    categories: trend.map(t => t.date)
                }
            });
        } else {
            trendChart = new ApexCharts(document.getElementById('chartTrend'), opts);
            trendChart.render();
        }
    }

    // ── OPERATOR CHART ──
    function updateOperatorChart(ops) {
        if (!ops.length) {
            document.getElementById('chartOperator').innerHTML = '<div class="empty-state">Belum ada data</div>';
            if (operatorChart) {
                operatorChart.destroy();
                operatorChart = null;
            }
            return;
        }

        const opts = {
            series: [{
                name: 'Total Rak',
                data: ops.map(o => o.total)
            }],
            chart: {
                type: 'bar',
                height: 180,
                background: 'transparent',
                toolbar: {
                    show: false
                },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 600
                },
            },
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    horizontal: true,
                    barHeight: '60%'
                }
            },
            dataLabels: {
                enabled: true,
                style: {
                    fontSize: '10px',
                    colors: ['#e2e8f0']
                }
            },
            xaxis: {
                categories: ops.map(o => o.nama),
                labels: {
                    style: {
                        colors: '#64748b',
                        fontSize: '10px'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#64748b',
                        fontSize: '10px'
                    },
                    maxWidth: 90
                }
            },
            colors: ['#6366f1'],
            grid: {
                borderColor: 'rgba(255,255,255,0.05)'
            },
            tooltip: {
                theme: 'dark',
                y: {
                    formatter: v => v + ' rak'
                }
            },
            theme: {
                mode: 'dark'
            },
        };

        if (operatorChart) {
            operatorChart.updateSeries([{
                data: ops.map(o => o.total)
            }]);
            operatorChart.updateOptions({
                xaxis: {
                    categories: ops.map(o => o.nama)
                }
            });
        } else {
            operatorChart = new ApexCharts(document.getElementById('chartOperator'), opts);
            operatorChart.render();
        }
    }

    // ── ACTIVITY FEED ──
    function updateActivity(items) {
        const feed = document.getElementById('activityFeed');
        if (!items || !items.length) {
            feed.innerHTML =
                '<div style="text-align:center; padding:20px; color:#475569; font-size:12px;">Belum ada aktivitas</div>';
            return;
        }

        feed.innerHTML = items.map(item => {
            let statusDot = 'status-dot';
            if (item.status === 'diterima') statusDot += ' status-green';
            else if (item.status === 'proses') statusDot += ' status-blue';
            else if (item.status === 'sebagian') statusDot += ' status-orange';
            else if (item.status === 'batal') statusDot += ' status-red';

            const isKosong = item.tipe === 'rak_kosong';
            const labelQty = isKosong ?
                `<span style="color:#f59e0b">${item.total_rak} Rak / ${item.total_palet} Palet (KOSONG)</span>` :
                `<span style="color:#64c8ff">${item.total_rak} Rak Isi</span>`;

            return `
                <div class="activity-item">
                    <div class="${statusDot}"></div>
                    <div class="activity-content">
                        <div class="activity-main" style="display:flex; justify-content:space-between; align-items:center;">
                            <b>${item.mobil}</b>
                            ${labelQty}
                        </div>
                        <div class="activity-meta" style="color:#cbd5e1; font-size:11px; margin-top:6px; line-height:1.4;">
                             <div style="display:flex; justify-content:space-between; background:rgba(255,255,255,0.03); padding:4px 8px; border-radius:4px;">
                                <span>📤 <b>KIRIM:</b> ${item.operator_kirim}</span>
                                <span style="color:#64c8ff">${item.jam_kirim} • ${item.tgl}</span>
                             </div>
                             <div style="display:flex; justify-content:space-between; background:rgba(255,255,255,0.01); padding:4px 8px; border-radius:4px; margin-top:2px;">
                                <span>📥 <b>TERIMA:</b> ${item.operator_terima}</span>
                                <span style="color:#4ade80">${item.jam_terima}</span>
                             </div>
                             <div style="margin-top:4px; padding:0 8px; color:#94a3b8; font-size:10px;">
                                🚛 Supir: ${item.supir}
                             </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    // ── AUTO REFRESH ──
    setInterval(loadData, 10000); // Refresh data tiap 10 detik

    // ── INIT ──
    document.addEventListener('DOMContentLoaded', loadData);
</script>
