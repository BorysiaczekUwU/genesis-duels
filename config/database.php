<?php
// Plik konfiguracyjny bazy danych

// Użyj zmiennych środowiskowych dla bezpieczeństwa
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'genesis_duels');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $db_host = DB_HOST;
        $db_name = DB_NAME;
        $db_user = DB_USER;
        $db_pass = DB_PASS;

        try {
            $this->conn = new PDO("mysql:host={$db_host};dbname={$db_name}", $db_user, $db_pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            // W rzeczywistej aplikacji loguj błąd, nie wyświetlaj go użytkownikowi
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['message' => 'Database connection error. Check server configuration.']);
            exit();
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}
