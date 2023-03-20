<?php
    // Mengambil koneksi database dari file config.php
    require_once('../connection.php');
    require_once('../validation/validation.php');

    // Inisialisasi pesan error
    $errors = array();

    // Validasi Input
    $input = [
        'user_nik' => $_POST['nik'] ?? '',
        'jam_keluar' => $_POST['out'] ?? '',
        'jam_kembali' => $_POST['in'] ?? ''
    ];
    validateInput($input);

    // Mengambil data dari form
    $nik = $_POST['nik'];
    $tanggal = date('Y-m-d');
    $out = $_POST['out'];
    $in = $_POST['in'];

    // insert ke tabel
    $query = "INSERT INTO izin_keluar (user_nik, tanggal, jam_keluar, jam_kembali) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssss', $nik, $tanggal, $out, $in);
    
    // cek hasilnya
    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(
            array(
                'message' => 'Izin berhasil diajukan. menunggu acc.',
                'status' => true
            )
        );
    } else {
        http_response_code(500);
        echo json_encode(
            array(
                'message' => 'Izin gagal diajukan',
                'error' => mysqli_error($conn),
                'status' => false
            )
        );
    }

?>