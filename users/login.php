<?php
    // Mengambil koneksi database dari file config.php
    require_once('../connection.php');
    require_once('../validation/validation.php');

    // Inisialisasi pesan error
    $errors = array();

    // Validasi Input
    $input = [
        'nik' => $_POST['nik'] ?? '',
        'password' => $_POST['password'] ?? ''
    ];
    validateInput($input);

    // Menyiapkan query untuk mengambil data user dari database dengan prepared statement
    $stmt = $conn->prepare("SELECT user.nik, password.password, user.salt FROM user INNER JOIN password on  user.nik = password.nik WHERE password.nik=?");
    $stmt->bind_param("s", $nik);

    // Mengambil data dari form
    $nik = $_POST['nik'];
    $password = $_POST['password'];

    // Menjalankan query untuk mengambil data user dari database
    $stmt->execute();
    $result = $stmt->get_result();

    // Jika user ditemukan, verifikasi password
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $hashed_password = $row['password'];

        if (password_verify($password, $hashed_password)) {
            date_default_timezone_set('Asia/Jakarta');

            // Jika password sesuai, buat token
            // Mendefinisikan waktu expired token (dalam detik)
            $token_expiration = 604800; // 1 minggu
            $expired_at = time() + $token_expiration;
            $expired_at_string = date('Y-m-d H:i:s', $expired_at);

            $token = bin2hex(random_bytes(16));
            $stmt = $conn->prepare("UPDATE user SET token=?, token_expired_at=? WHERE nik=?");
            $stmt->bind_param("sss", $token, $expired_at_string, $nik);
            $stmt->execute();

            // Kirim respon berhasil
            http_response_code(200);

            // Mengambil data user dari database untuk dimasukkan ke dalam respon
            $stmt = $conn->prepare("SELECT * FROM user WHERE nik=?");
            $stmt->bind_param("s", $nik);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            echo json_encode(array(
                'message' => 'Login berhasil.',
                'status' => true,
                'token' => $token,
                'data' => array(
                    'nama' => $row['name'],
                    'nik' => $row['nik'],
                    'nip' => $row['nip'],
                    'email' => $row['email'],
                    'gender' => $row['gender'],
                    'telp'=> $row['telp'],
                    'isAdmin' => $row['is_admin']
                )
            ));
        } else {
            // Jika password salah, kirim respon error
            http_response_code(401);
            echo json_encode(array(
                'message' => 'nik atau password salah.',
                'status' => false
            ));
        }
    } else {
        // Jika user tidak ditemukan, kirim respon error
        http_response_code(401);
        echo json_encode(array(
            'message' => 'nik atau password salah.',
            'status' => false
        ));
    }

    // Menutup prepared statement
    $stmt->close();

    // Menutup koneksi database
    $conn->close();
?>
