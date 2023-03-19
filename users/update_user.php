<?php
    // Mengambil koneksi database dari file config.php
    require_once('connection.php');
    require_once('validation/validation.php');

    // Inisialisasi pesan error
    $errors = array();

    // Mengambil data dari request POST
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Menentukan NIK yang akan diupdate
    $nik = $data['nik'];

    // Menghapus field NIK dari data agar tidak diupdate
    unset($data['nik']);

    // Membuat string untuk pernyataan SQL
    $sql = "UPDATE user SET ";
    foreach ($data as $key => $value) {
        $sql .= $key . " = ?, ";
    }
    $sql = rtrim($sql, ", "); // Menghapus koma terakhir
    $sql .= " WHERE nik=?";

    // Membuat array untuk binding parameter
    $param = array_values($data);
    $param[] = $nik;

    // Menyiapkan pernyataan SQL
    $stmt = $conn->prepare($sql);

    // Binding parameter ke pernyataan SQL
    $types = str_repeat('s', count($param)); // Menentukan tipe data binding parameter
    $stmt->bind_param($types, ...$param);

    // Menjalankan pernyataan SQL
    $stmt->execute();

    // Menutup prepared statement
    $stmt->close();

    // Kirim respon berhasil
    http_response_code(200);
    echo json_encode(array(
        'message' => 'Data user berhasil diupdate.',
        'status' => true
    ));

    // Menutup koneksi database
    $conn->close();
?>
