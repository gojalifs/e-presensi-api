<?php
    //define the constanta
    define('HOST','localhost');
    define('USER','admin');
    define('PASS','.lub17-T-DkEWJ02');
    define('DB','e-presensi');

    //connection to database
    $conn = mysqli_connect(HOST,USER,PASS,DB);
    if (!$conn) {
        die("Koneksi database gagal");
    }    
?>