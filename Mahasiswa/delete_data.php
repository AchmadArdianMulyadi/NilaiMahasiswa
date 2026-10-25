<?php
// Koneksi database
require_once __DIR__ . "/connect.php";

// Mengambil NIM dari form
$nim = $_POST['nim'];

// 1. Ambil nama file foto terlebih dahulu dari database berdasarkan NIM
$q_foto = mysqli_prepare($conn, "SELECT foto FROM data_mhsw WHERE nim = ?");
mysqli_stmt_bind_param($q_foto, "s", $nim);
mysqli_stmt_execute($q_foto);
$res_foto = mysqli_stmt_get_result($q_foto);

if ($row_foto = mysqli_fetch_assoc($res_foto)) {
    // Jika ada file foto dan fisiknya ada di folder, hapus filenya
    if (!empty($row_foto['foto']) && file_exists(__DIR__ . "/uploads/" . $row_foto['foto'])) {
        unlink(__DIR__ . "/uploads/" . $row_foto['foto']);
    }
}
mysqli_stmt_close($q_foto);

// 2. Query hapus data dari database
$sql = "DELETE FROM data_mhsw WHERE nim = ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $nim);

if (mysqli_stmt_execute($stmt)) {
    header("Location: users.php");
    exit;
} else {
    echo "Error: " . mysqli_stmt_error($stmt);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>