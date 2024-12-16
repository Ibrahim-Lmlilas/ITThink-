<?php

// Had class Database hiya li ghadi n7tajo bach nconnectiw l database dyalna
class Database {
    // Hna fin kayn les informations dyal connection
    // Host: fin kayna database (serveur local)
    private $host = "127.0.0.1";
    // Smiya dyal database li ghadi nkhdmo biha
    private $db_name = "mydb";
    // Username bach ndkhlo l database (par défaut: 'root')
    private $username = "root";
    // Password dyal connection (khawi f local)
    private $password = "";
    // Variable li ghadi t7bess connection
    private $conn;

    // Method li katsawr connection l database
    public function getConnection() {
        // Initialisation dyal connection b null
        $this->conn = null;

        try {
            // Hna fin kay3mer connection b PDO
            // PDO: PHP Data Objects, kaykhdem m3a bzaf dyal databases
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            // Hadi bach ila kan chi error, yt7bess l programme w ywerrina error
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Bach nkhdmo b UTF8 (7rof 3rbiya w emoji w...) 
            $this->conn->exec("set names utf8");
        } catch(PDOException $e) {
            // Ila kan chi mochkil f connection, ghadi iban hna
            echo "Connection error: " . $e->getMessage();
        }

        // Kan returniw connection bach nkhdmo biha f fichiers khrin
        return $this->conn;
    }
}
?>



