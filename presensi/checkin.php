<?php
    // Mengambil koneksi database dari file config.php
    require_once('../connection.php');
    require_once('../validation/validation.php');

    // Inisialisasi pesan error
    $errors = array();

    // Validasi Input
    $input = [
        'nik' => $_POST['nik'] ?? '',
        'jenis' => $_POST['jenis'] ?? '',
        'longitude' => $_POST['longitude'] ?? '',
        'latitude' => $_POST['latitude'] ?? '',
        'img_path' => $_POST['img_path'] ?? ''
    ];
    validateInput($input);

    switch ($_POST['jenis']) {
        case 'masuk':
        case 'keluar':
            break;
        default:
            echo json_encode(
                array(
                    'message' => 'Jenis presensi tidak valid.',
                    'status' => false
                )
            );
            exit();
    }


    // Mengambil data dari form
    $nik = $_POST['nik'];
    $jenis = $_POST['jenis'];
    $tanggal = date('Y-m-d');
    $jam = date('Y-m-d H:i:s');
    $longitude = $_POST['longitude'];
    $latitude = $_POST['latitude'];
    $img_path = $_POST['img_path'];

    // Mengecek apakah terdapat data presensi untuk nik dan tanggal yang sama
    $query = "SELECT * FROM presensi WHERE nik = ? AND tanggal = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $nik, $tanggal);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $query = "INSERT INTO presensi (nik, tanggal) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ss', $nik, $tanggal);
        $stmt->execute();
    }

    if ($stmt->affected_rows > 0) {
        $query = "SELECT MAX(id) AS last_id FROM presensi WHERE nik = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $nik);
        $stmt->execute();
        $result = $stmt->get_result();
        $lastId = $result->fetch_assoc();
        $id = $lastId['last_id'];

        $query = "SELECT jenis FROM (SELECT * FROM presensi_detail WHERE id_presensi = ? ORDER BY id DESC LIMIT 1) AS t WHERE jenis = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('is', $id, $jenis);
        $stmt->execute();
        $result = $stmt->get_result();

        // cek jika sudah check in/out
        if ($result->num_rows > 0) {
            echo json_encode(
                array(
                    'message' => 'Terjadi kesalahan saat menambahkan presensi detail. sudah pernah scan ' . $jenis,
                    'status' => false
                )
            );
            exit();
        }

        /// TODO add upload image
        // // Tentukan direktori penyimpanan gambar
        // $target_dir = "../picts/";
        // if (!is_dir($target_dir . $nik . '/')) {
        //     mkdir($target_dir . $nik . '/', 0777, true);
        // }


        // // Buat nama file unik untuk gambar dengan menambahkan timestamp di depan nama file
        // $target_file = $target_dir . time() . '_' . basename($_FILES["img_path"]["name"]);

        // // Simpan gambar ke direktori penyimpanan
        // if (move_uploaded_file($_FILES["img_path"]["tmp_name"], $target_file)) {
        //     // Jika berhasil, simpan path gambar ke variabel $img_path
        //     $img_path = $target_file;
        // } else {
        //     // Jika gagal, tampilkan pesan error
        //     echo json_encode(array(
        //         'message' => 'Terjadi kesalahan saat mengunggah gambar.',
        //         'status' => false
        //     ));
        //     exit();
        // }


        // Memasukkan data presensi detail ke dalam tabel presensi_detail
        $query = "INSERT INTO presensi_detail (id_presensi, jenis, jam, longitude, latitude, img_path) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('isssss', $id, $jenis, $jam, $longitude, $latitude, $img_path);
        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(
                array(
                    'message' => 'Presensi berhasil ditambahkan.',
                    'status' => true
                )
            );
        } else {
            http_response_code(500);
            echo json_encode(
                array(
                    'message' => 'Terjadi kesalahan saat menambahkan presensi detail.',
                    'status' => false
                )
            );
        }
    } else {
        http_response_code(500);
        echo json_encode(
            array(
                'message' => 'Terjadi kesalahan saat menambahkan presensi.',
                'status' => false
            )
        );
    }

    // Menutup koneksi database
    $conn->close();
?>