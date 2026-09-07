<?php
/**
 * =====================================================
 * KONFIG.PHP - Konfigurasi Aplikasi ID Card / Passport
 * =====================================================
 * Semua pengaturan aplikasi (database, info AWS dummy, dll)
 * ada di file ini. Silakan ubah sesuai kebutuhan environment
 * Anda (localhost / server / AWS EC2).
 */

// ---------- KONFIGURASI DATABASE ----------
define('DB_HOST', 'localhost');      // Host MySQL, mis: localhost atau IP RDS
define('DB_PORT', '3306');           // Port MySQL
define('DB_USER', 'root');           // Username MySQL
define('DB_PASS', '');               // Password MySQL
define('DB_NAME', 'db_idcard');      // Nama database (akan dibuat otomatis jika belum ada)
define('DB_TABLE', 'biodata');       // Nama tabel (akan dibuat otomatis jika belum ada)

// ---------- INFO ENVIRONMENT AWS ----------
// Info EC2 (Instance ID, Public IP, Region/AZ) diambil OTOMATIS secara
// real-time dari AWS EC2 Instance Metadata Service (IMDSv2) melalui
// fungsi getAwsInfo() di bawah — TIDAK ada nilai dummy/hardcode di sini.
// Jika aplikasi tidak berjalan di EC2, nilai-nilai tersebut akan
// ditampilkan sebagai "-" (bukan data palsu).
// RDS Endpoint yang ditampilkan = nilai DB_HOST asli di atas.

// ---------- KONEKSI & AUTO-CREATE DATABASE/TABLE ----------
function getDbConnection()
{
    // 1. Konek ke MySQL server dulu (tanpa nama database)
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, '', (int) DB_PORT);
    if ($conn->connect_error) {
        die('Koneksi ke MySQL server gagal: ' . $conn->connect_error);
    }

    // 2. Buat database otomatis jika belum ada
    $conn->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");

    // 3. Pilih database
    $conn->select_db(DB_NAME);

    // 4. Buat tabel otomatis jika belum ada
    $sqlTable = "CREATE TABLE IF NOT EXISTS `" . DB_TABLE . "` (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        kelas VARCHAR(50) NOT NULL,
        absen VARCHAR(10) NOT NULL,
        jk ENUM('Laki-Laki','Perempuan') NOT NULL,
        no_hp VARCHAR(20) NOT NULL,
        foto_path VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    if (!$conn->query($sqlTable)) {
        die('Gagal membuat tabel: ' . $conn->error);
    }

    // 5. Migrasi otomatis: pastikan semua kolom yang dibutuhkan ada
    //    (berguna jika tabel sudah terlanjur dibuat dengan struktur lama)
    $requiredColumns = [
        'nama'       => "VARCHAR(100) NOT NULL",
        'kelas'      => "VARCHAR(50) NOT NULL",
        'absen'      => "VARCHAR(10) NOT NULL",
        'jk'         => "ENUM('Laki-Laki','Perempuan') NOT NULL",
        'no_hp'      => "VARCHAR(20) NOT NULL",
        'foto_path'  => "VARCHAR(255) DEFAULT NULL",
        'created_at' => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
    ];

    $existingColumns = [];
    $res = $conn->query("SHOW COLUMNS FROM `" . DB_TABLE . "`");
    while ($row = $res->fetch_assoc()) {
        $existingColumns[] = $row['Field'];
    }

    foreach ($requiredColumns as $colName => $colDef) {
        if (!in_array($colName, $existingColumns)) {
            $conn->query("ALTER TABLE `" . DB_TABLE . "` ADD COLUMN `" . $colName . "` " . $colDef);
        }
    }

    return $conn;
}

/**
 * Ambil informasi environment AWS.
 * Jika aplikasi benar-benar berjalan di EC2 (mis. AWS Academy Lab),
 * fungsi ini akan membaca data ASLI dari EC2 Instance Metadata Service
 * versi 2 (IMDSv2). Jika tidak terdeteksi (mis. dijalankan di
 * localhost/laptop), maka akan fallback ke nilai dummy di atas.
 */
function getAwsInfo()
{
    $info = [
        'ec2_id'       => '-',
        'ec2_ip'       => '-',
        'ec2_private'  => '-',
        'rds_endpoint' => (defined('DB_HOST') && DB_HOST !== '') ? DB_HOST : '-', // endpoint asli sesuai konfigurasi koneksi DB
        'region'       => '-',
        'az'           => '-',
        'source'       => 'unknown', // akan berubah jadi 'real' jika berhasil baca metadata EC2
    ];

    $timeout = 0.4; // detik, biar tidak lama nunggu kalau memang bukan di EC2

    // Step 1: minta token IMDSv2
    $token = @file_get_contents('http://169.254.169.254/latest/api/token', false, stream_context_create([
        'http' => [
            'method'        => 'PUT',
            'header'        => "X-aws-ec2-metadata-token-ttl-seconds: 21600\r\n",
            'timeout'       => $timeout,
            'ignore_errors' => true,
        ],
    ]));

    if ($token) {
        $ctx = stream_context_create([
            'http' => [
                'header'        => "X-aws-ec2-metadata-token: $token\r\n",
                'timeout'       => $timeout,
                'ignore_errors' => true,
            ],
        ]);

        $instanceId = @file_get_contents('http://169.254.169.254/latest/meta-data/instance-id', false, $ctx);
        $publicIp   = @file_get_contents('http://169.254.169.254/latest/meta-data/public-ipv4', false, $ctx);
        $privateIp  = @file_get_contents('http://169.254.169.254/latest/meta-data/local-ipv4', false, $ctx);
        $az         = @file_get_contents('http://169.254.169.254/latest/meta-data/placement/availability-zone', false, $ctx);

        // Jika instance-id berhasil terbaca, berarti kita benar-benar di EC2
        if ($instanceId) {
            $info['ec2_id'] = $instanceId;
            $info['source'] = 'real';

            $info['ec2_ip']      = $publicIp ?: '-';
            $info['ec2_private'] = $privateIp ?: '-';

            if ($az) {
                $info['az']     = $az;
                $info['region'] = substr($az, 0, -1); // hapus huruf zona terakhir, mis "ap-southeast-1a" -> "ap-southeast-1"
            }
        } else {
            $info['source'] = 'unavailable';
        }
    } else {
        $info['source'] = 'unavailable';
    }

    return $info;
}
