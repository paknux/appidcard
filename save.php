<?php
header('Content-Type: application/json');
require_once 'konfig.php';

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    $nama   = trim($_POST['nama'] ?? '');
    $kelas  = trim($_POST['kelas'] ?? '');
    $absen  = trim($_POST['absen'] ?? '');
    $jk     = trim($_POST['jk'] ?? '');
    $no_hp  = trim($_POST['no_hp'] ?? '');

    if ($nama === '' || $kelas === '' || $absen === '' || $jk === '' || $no_hp === '') {
        throw new Exception('Semua field wajib diisi.');
    }
    if (!in_array($jk, ['Laki-Laki', 'Perempuan'])) {
        throw new Exception('Jenis kelamin tidak valid.');
    }

    // Tentukan foto berdasarkan JK (foto simulasi / bukan hasil kamera asli)
    $foto_path = ($jk === 'Laki-Laki') ? 'img/foto-male.jpg' : 'img/foto-female.jpg';

    $conn = getDbConnection();

    $stmt = $conn->prepare(
        "INSERT INTO `" . DB_TABLE . "` (nama, kelas, absen, jk, no_hp, foto_path) VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param('ssssss', $nama, $kelas, $absen, $jk, $no_hp, $foto_path);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    $awsInfo = getAwsInfo();
    $regionAz = ($awsInfo['region'] !== '-' && $awsInfo['az'] !== '-')
        ? $awsInfo['region'] . ' / ' . $awsInfo['az']
        : '-';

    $response['success'] = true;
    $response['data'] = [
        'nama'      => htmlspecialchars($nama),
        'kelas'     => htmlspecialchars($kelas),
        'absen'     => htmlspecialchars($absen),
        'jk'        => htmlspecialchars($jk),
        'no_hp'     => htmlspecialchars($no_hp),
        'foto_path' => $foto_path,
        'aws'       => [
            'ec2_id'       => $awsInfo['ec2_id'],
            'ec2_ip'       => $awsInfo['ec2_ip'],
            'rds_endpoint' => $awsInfo['rds_endpoint'],
            'region'       => $regionAz,
            'source'       => $awsInfo['source'], // 'real' atau 'unavailable'
        ],
    ];
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} catch (mysqli_sql_exception $e) {
    $response['message'] = 'DB Error: ' . $e->getMessage();
}

echo json_encode($response);