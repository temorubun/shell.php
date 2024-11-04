<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Izinkan Akses Host</title>
</head>
<body>
    <h1>Izinkan Akses Host</h1>
    <form method="POST">
        <label for="allowed_host">Masukkan IP Host yang Diizinkan:</label>
        <input type="text" name="allowed_host" id="allowed_host" required>
        <button type="submit">Submit</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Konfigurasi koneksi ke database
        $server = "localhost"; // koneksi ke server MariaDB lokal
        $username = "root";
        $password = ""; // sesuaikan dengan password root MariaDB Anda

        // Ambil IP host yang diizinkan dari input pengguna
        $allowed_host = $_POST['allowed_host'];

        try {
            // Membuat koneksi ke MariaDB
            $pdo = new PDO("mysql:host=$server", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Query untuk mengizinkan akses dari IP tertentu tanpa password
            $query = "GRANT ALL PRIVILEGES ON *.* TO 'root'@'$allowed_host' IDENTIFIED BY 'root' WITH GRANT OPTION;";
            $pdo->exec($query);

            // Menyimpan perubahan
            $pdo->exec("FLUSH PRIVILEGES;");
            
            echo "Akses berhasil diberikan untuk host $allowed_host dengan username root & password root.";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    ?>
</body>
</html>
