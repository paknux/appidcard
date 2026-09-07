<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title>ID Card Digital</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="app-wrap">

    <!-- ===================== VIEW 0: WARNING BUKAN HP ===================== -->
    <div id="viewBlocked" class="single-card d-none">
        <div class="card shadow-lg h-100">
            <div class="card-body d-flex flex-column align-items-center justify-content-center text-center p-4">
                <div class="blocked-icon mb-3">📵</div>
                <h5 class="text-warning fw-bold mb-2">Akses Tidak Didukung</h5>
                <p class="text-light small mb-1">
                    Aplikasi ini hanya bisa diakses melalui
                    <span class="text-warning fw-bold">browser HP (smartphone)</span>
                    yang memiliki kamera.
                </p>
                <p class="text-light small mb-0">
                    Silakan buka kembali halaman ini menggunakan HP Anda.
                </p>
            </div>
        </div>
    </div>

    <!-- ===================== VIEW 1: FORM BIODATA ===================== -->
    <div id="viewForm" class="single-card">
        <div class="card shadow-lg h-100">
            <div class="card-body d-flex flex-column">
                <h5 class="text-center mb-2 title-app">📇 Buat ID Card</h5>

                <form id="formBiodata" class="flex-grow-1 d-flex flex-column">
                    <div class="mb-2">
                        <label class="form-label mb-1">Nama Lengkap</label>
                        <input type="text" class="form-control form-control-sm" name="nama" required maxlength="100">
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label mb-1">Kelas</label>
                            <input type="text" class="form-control form-control-sm" name="kelas" required maxlength="50">
                        </div>
                        <div class="col-6">
                            <label class="form-label mb-1">No. Absen</label>
                            <input type="text" class="form-control form-control-sm" name="absen" required maxlength="10">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label mb-1">Jenis Kelamin (JK)</label>
                        <select class="form-select form-select-sm" name="jk" required>
                            <option value="" selected disabled>-- Pilih JK --</option>
                            <option value="Laki-Laki">Laki-Laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label mb-1">Nomer HP</label>
                        <input type="tel" class="form-control form-control-sm" name="no_hp" required maxlength="20" placeholder="08xxxxxxxxxx">
                    </div>

                    <div class="mt-auto">
                        <button type="button" id="btnAmbilFoto" class="btn btn-warning w-100 fw-bold">
                            📷 Ambil Foto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===================== VIEW 2: SIMULASI KAMERA ===================== -->
    <div id="viewCamera" class="single-card d-none">
        <div class="card shadow-lg h-100">
            <div class="card-body d-flex flex-column align-items-center justify-content-center camera-box">
                <div class="camera-frame mb-3">
                    <div class="scan-line"></div>
                    <div class="camera-icon">📷</div>
                </div>
                <p class="text-warning fw-bold mb-1" id="camStatus">Membuka kamera...</p>
                <div class="progress w-100" style="height:6px;">
                    <div id="camProgress" class="progress-bar bg-warning" role="progressbar" style="width:0%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== VIEW 3: ID CARD ===================== -->
    <div id="viewCard" class="single-card d-none">
        <div class="card shadow-lg h-100 id-card">
            <div class="card-body d-flex flex-column p-2">

                <div class="text-center id-header">
                    <div class="small fw-bold text-warning">ID Card</div>
                    <div class="tiny text-muted">KARTU IDENTITAS PELAJAR</div>
                </div>

                <div class="text-center my-1">
                    <img id="cardFoto" src="" class="foto-utama" alt="Foto">
                </div>

                <div class="biodata-box flex-grow-1">
                    <table class="table-biodata">
                        <tr><td>Nama</td><td>:</td><td id="cNama"></td></tr>
                        <tr><td>Kelas</td><td>:</td><td id="cKelas"></td></tr>
                        <tr><td>Absen</td><td>:</td><td id="cAbsen"></td></tr>
                        <tr><td>JK</td><td>:</td><td id="cJk"></td></tr>
                        <tr><td>No. HP</td><td>:</td><td id="cHp"></td></tr>
                    </table>
                </div>

                <div class="aws-box">
                    <div class="tiny text-info fw-bold mb-1 d-flex justify-content-between align-items-center">
                        <span>⚙ AWS ENVIRONMENT INFO</span>
                        <span id="awsSource" class="badge"></span>
                    </div>
                    <div class="tiny aws-line"><span>EC2 Instance ID</span><span id="awsEc2Id"></span></div>
                    <div class="tiny aws-line"><span>EC2 Public IP</span><span id="awsEc2Ip"></span></div>
                    <div class="tiny aws-line"><span>RDS Endpoint</span><span id="awsRds" class="text-truncate"></span></div>
                    <div class="tiny aws-line"><span>Region / AZ</span><span id="awsRegion"></span></div>
                </div>

                <button id="btnUlangi" class="btn btn-outline-warning btn-sm w-100 mt-1">🔄 Buat Ulang</button>
            </div>
        </div>
    </div>

</div>

<script>
    // ===================== DETEKSI DEVICE (HP vs PC/TABLET) =====================
    (function () {
        const ua = navigator.userAgent || navigator.vendor || '';

        // Deteksi tablet (iPad, Android tablet, dsb) -> dianggap TIDAK didukung
        const isTablet = /iPad|Android(?!.*Mobile)|Tablet|Nexus 7|Nexus 10|KFAPWI/i.test(ua);

        // Deteksi HP (Android Mobile, iPhone, iPod, Windows Phone, dsb)
        const isMobile = /Android.*Mobile|iPhone|iPod|Windows Phone|BlackBerry|IEMobile|Opera Mini/i.test(ua);

        // HP dianggap valid hanya jika terdeteksi mobile DAN bukan tablet,
        // serta punya kemampuan sentuh (touch) sebagai pengaman tambahan.
        const hasTouch = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);

        const isSupportedPhone = isMobile && !isTablet && hasTouch;

        const viewBlocked = document.getElementById('viewBlocked');
        const viewForm     = document.getElementById('viewForm');

        if (!isSupportedPhone) {
            // Sembunyikan semua view aplikasi, tampilkan hanya warning
            document.getElementById('viewCamera').classList.add('d-none');
            document.getElementById('viewCard').classList.add('d-none');
            viewForm.classList.add('d-none');
            viewBlocked.classList.remove('d-none');

            // Tandai agar app.js tidak menjalankan logika apapun
            window.__APP_BLOCKED__ = true;
        }
    })();
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<script src="js/app.js"></script>
</body>
</html>