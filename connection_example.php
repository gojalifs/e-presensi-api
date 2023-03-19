<?php
    //define the constanta
    define('HOST','localhost');
    define('USER','admin');
    define('PASS','password');
    define('DB','e-presensi');

    //connection to database
    $conn = mysqli_connect(HOST,USER,PASS,DB);
    if (!$conn) {
        die("Koneksi database gagal");
    }    
?>