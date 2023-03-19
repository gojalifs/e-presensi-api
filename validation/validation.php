<?php
    function validateInput($inputs) {
        $errors = [];

        foreach ($inputs as $key => $value) {
            if (empty($value)) {
                $errors[] = $key . ' tidak boleh kosong.';
            } else {
                switch ($key) {
                    case 'name':
                        if (!ctype_alnum($value)) {
                            $errors[] = 'Username hanya boleh terdiri dari huruf dan angka.';
                        }
                        break;
                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[] = 'Format email tidak valid.';
                        }
                        break;
                    case 'password':
                        // Tambahkan validasi password di sini
                        break;
                    default:
                        // Tidak melakukan validasi untuk input lain
                        break;
                }
            }
        }

        if (!empty($errors)) {
            echo json_encode($errors);
            exit(1);
        }
    }

?>