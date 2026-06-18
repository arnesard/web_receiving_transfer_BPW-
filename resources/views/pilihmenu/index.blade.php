<!DOCTYPE html>
<html lang="id">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Menu</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <script src="{{ asset('js/lucide.min.js') }}"></script>

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: radial-gradient(circle at top, #1e293b, #020617);
            color: #e2e8f0;
        }

        .container-box {
            max-width: 420px;
            margin: auto;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .title {
            text-align: center;
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 30px;
            letter-spacing: 1px;
        }

        .menu {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .card-menu {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 18px;
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(10px);
            text-decoration: none;
            color: #e2e8f0;
            transition: 0.25s ease;
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        .card-menu:hover {
            transform: translateY(-5px);
            border-color: #3b82f6;
            background: rgba(59, 130, 246, 0.15);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.25);
        }

        .icon-box {
            width: 45px;
            height: 45px;
            border-radius: 14px;
            background: rgba(59, 130, 246, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .menu-text {
            font-size: 15px;
            font-weight: 600;
        }

        /* subtle glow effect */
        .card-menu::after {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 18px;
            opacity: 0;
            transition: 0.3s;
        }

        .card-menu:hover::after {
            opacity: 1;
        }

        .logout-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;

            padding: 14px;
            margin-top: 10px;

            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.08);

            background: rgba(239, 68, 68, 0.08);
            color: #fca5a5;

            font-weight: 600;
            font-size: 14px;

            cursor: pointer;
            transition: 0.25s ease;
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.18);
            border-color: rgba(239, 68, 68, 0.4);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.2);
        }
    </style>


</head>

<body>
    <div class="container-box">
        <div class="title">
            PILIH MENU
        </div>
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert"
                style="background: rgba(34, 197, 94, 0.2); color: #86efac; border: 1px solid rgba(34, 197, 94, 0.4);">
                {{ session('success') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"
                    aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert"
                style="background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.4);">
                {{ session('error') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"
                    aria-label="Close"></button>
            </div>
        @endif
        <div class="menu">
            <a href="{{ route('penerimaan.index') }}" class="card-menu">
                <div class="icon-box">
                    <i data-lucide="package"></i>
                </div>
                <div class="menu-text">
                    Penerimaan Produksi
                </div>
            </a>
            <a href="{{ route('transfer.index') }}" class="card-menu">
                <div class="icon-box">
                    <i data-lucide="repeat"></i>
                </div>
                <div class="menu-text">
                    Monitoring Transfer Rak
                </div>
            </a>
            <a href="{{ route('overtime.index') }}" class="card-menu">
                <div class="icon-box">
                    <i data-lucide="clock"></i>
                </div>
                <div class="menu-text">
                    Input Lembur
                </div>
            </a>
            <a href="{{ route('so_karantina.menu') }}" class="card-menu">
                <div class="icon-box">
                    <i data-lucide="shield-alert"></i>
                </div>
                <div class="menu-text">
                    SO Karantina
                </div>
            </a>
            <a href="#" class="card-menu" data-bs-toggle="modal" data-bs-target="#uploadBstbModal">
                <div class="icon-box">
                    <i data-lucide="file"></i>
                </div>
                <div class="menu-text">
                    SO Karantina (Upload BSTB Barcode)
                </div>
            </a>
            <form action="{{ route('logout') }}" method="POST" style="margin-top: 30px;">
                @csrf
                <button type="submit" class="logout-btn">
                    <i data-lucide="log-out"></i>
                    <span>Logout</span>
                </button>
            </form>

        </div>

    </div>

    <div class="modal fade" id="uploadBstbModal" tabindex="-1" aria-labelledby="uploadBstbModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content"
                style="background: #1e293b; color: #e2e8f0; border: 1px solid rgba(255, 255, 255, 0.1);">
                <div class="modal-header" style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                    <h5 class="modal-title" id="uploadBstbModalLabel">Upload BSTB Barcode</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <form action="{{ route('karantina.upload_bstb') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="file_excel" class="form-label">Pilih File Excel (.xlsx / .xls)</label>
                            <input class="form-control" type="file" id="file_excel" name="file_excel"
                                accept=".xlsx, .xls, .csv" required
                                style="background: rgba(255,255,255,0.05); color: #fff; border: 1px solid rgba(255,255,255,0.1);">
                        </div>
                        <small style="color: #94a3b8; display: block; line-height: 1.5; margin-top: 5px;">
                            <strong>Format urutan kolom:</strong><br>
                            tgl | plant | opr | oprname | item | bcditem | catatan | q_1 | q_2 | q_3 | q_4 | q_5 | q_6 |
                            q_7 | q_8 | q_9 | q_10 | q_11 | q_12 | q_13 | q_14 | q_15 | q_16 | total | r_1 | r_2 | r_3 |
                            r_4 | r_5 | r_6 | r_7 | r_8 | r_9 | r_10 | r_11 | r_12 | r_13 | r_14 | r_15 | r_16
                        </small>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid rgba(255, 255, 255, 0.1);">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" style="background: #3b82f6; border: none;">Upload
                            File</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>

</body>

</html>
