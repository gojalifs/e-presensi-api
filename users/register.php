<?php
    // Mengambil koneksi database dari file config.php
    require_once('connection.php');
    require_once('validation/validation.php');

    // Inisialisasi pesan error
    $errors = array();

    // Menyiapkan query untuk memasukkan data user ke database dengan prepared statement
    $stmt = $conn->prepare("INSERT INTO user (name, nik, email, telp, salt) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $name, $nik, $email, $telp, $salt);
    
    $stmtpwd = $conn->prepare("INSERT INTO password (nik, password) VALUES (?, ?)");
    $stmtpwd->bind_param("ss", $nik, $hashed_password);

    // Validasi Input
    $input = [
        'name' => $_POST['name'] ?? '',
        'nik' => $_POST['nik'] ?? '',
        'email' => $_POST['email'] ?? '',
        'gender' => $_POST['gender'] ?? '',
        'telp' => $_POST['telp'] ?? '',
        'password' => $_POST['password'] ?? ''
    ];
    validateInput($input);

    // Mengambil data dari form
    $name = $_POST['name'];
    $nik = $_POST['nik'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $telp = $_POST['telp'];
    $password = $_POST['password'];

    // Mengambil data input
    $nik = mysqli_real_escape_string($conn, $nik);
    $email = mysqli_real_escape_string($conn, $email);
    $password =mysqli_real_escape_string($conn, $password);
    
    // Enkripsi password
    $options = [
        'memory_cost' => PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
        'time_cost' => PASSWORD_ARGON2_DEFAULT_TIME_COST,
        'threads' => PASSWORD_ARGON2_DEFAULT_THREADS,
    ];

    $hashed_password = password_hash($password, PASSWORD_ARGON2I, $options);

    // Menjalankan query untuk memasukkan data user ke database
    $result = $stmt->execute();
    $respwd = $stmtpwd->execute();



    // Jika query berhasil dijalankan, kirim respon berhasil
    if ($result && $respwd) {
        http_response_code(201);
        echo json_encode(array(
            'message' => 'User berhasil didaftarkan.', 
            'status' => true
    ));
    } else {
        // Jika query gagal dijalankan, kirim respon error
        http_response_code(500);
        
        // Mengecek apakah terjadi error duplicate
        if ($result === false && $conn->errno === 1062) {
            http_response_code(400);
            echo json_encode(array(
                'message' => 'NIK atau Email sudah digunakan.',
                'status' => false
            ));
            
        }
        
        echo json_encode(array(
            'message' => 'Terjadi kesalahan saat mendaftarkan user.',
            'status' => false
    ));
    }

    // Menutup prepared statement
    $stmt->close();

    // Menutup koneksi database
    $conn->close();
?>