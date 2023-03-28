<?php
    // Mengambil koneksi database dari file config.php
    require_once('../connection.php');

    // Inisialisasi pesan error
    $errors = array();

    // Menyiapkan query untuk mencari data user di database dengan prepared statement
    $stmt = $conn->prepare("SELECT * FROM user WHERE nik=?");
    $stmt->bind_param("s", $nik);
    
    // Mengambil data dari form
    $nik = $_POST['nik'];
    $password = $_POST['password'];

    // Mengambil data input
    $nik = mysqli_real_escape_string($conn, $nik);
    $password = mysqli_real_escape_string($conn, $password);
    
    // Menjalankan query untuk mencari data user di database
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Memeriksa apakah data user ditemukan dan passwordnya cocok
    if ($user && password_verify($password, $user['password'])) {
        // Jika cocok, kirim respon berhasil
        http_response_code(200);
        echo json_encode(array(
            'message' => 'Login berhasil.', 
            'status' => true
        ));
    } else {
        // Jika tidak cocok, kirim respon error
        http_response_code(401);
        echo json_encode(array(
            'message' => 'NIK atau password salah.',
            'status' => false
        ));
    }

    // Menutup prepared statement
    $stmt->close();

    // Menutup koneksi database
    $conn->close();
?>
