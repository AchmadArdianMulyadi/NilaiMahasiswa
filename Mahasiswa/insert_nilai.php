<?php
// Koneksi ke database
require_once __DIR__ . "/connect.php";

$nim = trim($_POST['nim']);
$name = $_POST['name'];
$nilaia = $_POST['nilaia'];

// 1. Validasi panjang NIM maksimal 10 karakter
if (strlen($nim) > 10) {
    echo "<script>alert('NIM tidak boleh lebih dari 10 karakter!'); window.history.back();</script>";
    exit;
}

// 2. Validasi Cek NIM Duplikat
$cek_nim = mysqli_query($conn, "SELECT nim FROM data_mhsw WHERE nim = '$nim'");
if (mysqli_num_rows($cek_nim) > 0) {
    echo "<script>alert('Gagal! NIM tersebut sudah terdaftar di database.'); window.history.back();</script>";
    exit;
}

// Logika rentang nilai huruf dan predikat
if ($nilaia >= 80.51) {
    $nilai_hrf = "A";
    $predikat = "Sangat Memuaskan";
} elseif ($nilaia >= 65.61) {
    $nilai_hrf = "B";
    $predikat = "Memuaskan";
} elseif ($nilaia >= 50.51) {
    $nilai_hrf = "C";
    $predikat = "Cukup";
} elseif ($nilaia >= 34.51) {
    $nilai_hrf = "D";
    $predikat = "Kurang";
} else {
    $nilai_hrf = "E";
    $predikat = "Tidak Lulus";
}

// Logika Upload Foto
$nama_foto = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $file_tmp = $_FILES['foto']['tmp_name'];
    $file_name = $_FILES['foto']['name'];
    $nama_foto = time() . '_' . basename($file_name);
    move_uploaded_file($file_tmp, __DIR__ . "/uploads/" . $nama_foto);
}

// Query insert data
$sql = "INSERT INTO data_mhsw (nim, nama_mhsw, nilai_akhir, nilai_hrf, predikat, foto, created_at, updated_at)
        VALUES ('$nim', '$name', '$nilaia', '$nilai_hrf', '$predikat', '$nama_foto', NOW(), NOW())";

if (mysqli_query($conn, $sql)) {
  header('Location: users.php');
  exit();
} else {
  echo "Error: " . $sql . "<br>" . mysqli_error($conn);
}

mysqli_close($conn);
?>