<?php

require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $email;
    public $password;
    public $level;
    public $experience;
    public $credits;
    public $shards;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    // Rejestracja użytkownika
    public function register() {
        $query = "INSERT INTO " . $this->table_name . " SET username=:username, email=:email, password=:password";

        $stmt = $this->conn->prepare($query);

        // Czyszczenie danych
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = htmlspecialchars(strip_tags($this->password));

        // Haszowanie hasła
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);

        // Bindowanie parametrów
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $password_hash);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Logowanie użytkownika
    public function login() {
        $query = "SELECT id, username, password FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        $num = $stmt->rowCount();

        if ($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->username = $row['username'];
            $password_hash = $row['password'];

            if (password_verify($this->password, $password_hash)) {
                return true;
            }
        }
        return false;
    }

    // Pobieranie profilu użytkownika
    public function getProfile($id) {
         $query = "SELECT id, username, level, experience, credits, shards, avatar FROM " . $this->table_name . " WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        return $stmt;
    }

    // Sprawdzanie czy email istnieje
    public function emailExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return true;
        }
        return false;
    }
}
