<?php
    // Mengambil koneksi database dari file config.php
    require_once('connection.php');
    require_once('validation/validation.php');

    // Inisialisasi pesan error
    $errors = array();

    // Menentukan NIK yang akan diupdate
    $nik = $_POST['nik'];
    $pwd = $_POST['old_pwd'];
    $npwd = $_POST['new_pwd'];

    // get old password
    $oldpwdStmt = $conn->prepare("SELECT user.nik, password.password FROM password INNER JOIN user ON password.nik = user.nik where user.nik = ?");
    $oldpwdStmt->bind_param('s', $nik);
    $oldpwdStmt->execute();
    $resultOldPwd = $oldpwdStmt->get_result();

    if ($resultOldPwd->num_rows == 1) {
        $row = $resultOldPwd->fetch_assoc();
        $hashed_password = $row['password'];
        echo $hashed_password;
        if (password_verify($pwd, $hashed_password)) {            
            // hashing password baru
            // Enkripsi password
            $options = [
                'memory_cost' => PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
                'time_cost' => PASSWORD_ARGON2_DEFAULT_TIME_COST,
                'threads' => PASSWORD_ARGON2_DEFAULT_THREADS,
            ];            
            $new_hashed_password = password_hash($npwd, PASSWORD_ARGON2I, $options);
           
            // buat statement baru untuk update password
            $stmt = $conn->prepare("UPDATE password set password = ? WHERE nik = $nik");
            $stmt->bind_param('s', $new_hashed_password);
            $result = $stmt->execute();

            if ($result) {   
                echo json_encode(array(
                    'message' => 'Password sukses diubah',
                    'status' => true
                ));
            }

        }else {
            echo json_encode(array(
                'message' => 'Password anda salah',
                'status' => false
            ));
        }
    }
    

    // Menutup koneksi database
    $conn->close();
?>
