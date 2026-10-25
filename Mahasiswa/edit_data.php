<?php
// Koneksi database
require_once __DIR__ . "/connect.php";

$nim_lama = mysqli_real_escape_string($conn, $_POST['nim_lama']);
$nim = trim(mysqli_real_escape_string($conn, $_POST['nim']));
$nama = mysqli_real_escape_string($conn, $_POST['name']);
$nilai = $_POST['nilaia'];

// 1. Validasi panjang NIM maksimal 10 karakter
if (strlen($nim) > 10) {
    echo "<script>alert('NIM tidak boleh lebih dari 10 karakter!'); window.history.back();</script>";
    exit;
}

// 2. Validasi Cek NIM Duplikat (Hanya jika NIM diubah dari NIM lama)
if ($nim !== $nim_lama) {
    $cek_nim = mysqli_query($conn, "SELECT nim FROM data_mhsw WHERE nim = '$nim'");
    if (mysqli_num_rows($cek_nim) > 0) {
        echo "<script>alert('Gagal! NIM tersebut sudah digunakan oleh mahasiswa lain.'); window.history.back();</script>";
        exit;
    }
}

// Validasi nilai
if (!is_numeric($nilai) || $nilai < 0 || $nilai > 100) {
    echo "Nilai tidak valid! Masukkan nilai 0 - 100";
    exit;
}

// Menentukan nilai huruf dan predikat
if ($nilai >= 80.51) {
    $nilai_hrf = "A";
    $predikat = "Sangat Memuaskan";
} elseif ($nilai >= 65.61) {
    $nilai_hrf = "B";
    $predikat = "Memuaskan";
} elseif ($nilai >= 50.51) {
    $nilai_hrf = "C";
    $predikat = "Cukup";
} elseif ($nilai >= 34.51) {
    $nilai_hrf = "D";
    $predikat = "Kurang";
} else {
    $nilai_hrf = "E";
    $predikat = "Tidak Lulus";
}

// Cek apakah user mengunggah foto baru
$update_foto_sql = "";
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $file_tmp = $_FILES['foto']['tmp_name'];
    $file_name = $_FILES['foto']['name'];
    $nama_foto = time() . '_' . basename($file_name);
    
    move_uploaded_file($file_tmp, __DIR__ . "/uploads/" . $nama_foto);

    // Ambil nama foto lama dari database untuk dihapus dari folder fisik
    $q_old = mysqli_query($conn, "SELECT foto FROM data_mhsw WHERE nim = '$nim_lama'");
    $d_old = mysqli_fetch_assoc($q_old);
    if (!empty($d_old['foto']) && file_exists(__DIR__ . "/uploads/" . $d_old['foto'])) {
        unlink(__DIR__ . "/uploads/" . $d_old['foto']);
    }

    $update_foto_sql = ", foto = '$nama_foto'";
}

// Query update data
$sql = "UPDATE data_mhsw SET
        nim = '$nim',
        nama_mhsw = '$nama',
        nilai_akhir = '$nilai',
        nilai_hrf = '$nilai_hrf',
        predikat = '$predikat',
        updated_at = NOW()
        $update_foto_sql
        WHERE nim = '$nim_lama'";

if (mysqli_query($conn, $sql)) {
    header("Location: users.php");
    exit;
} else {
    echo "Error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>