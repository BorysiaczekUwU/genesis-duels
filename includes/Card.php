<?php

class Card {
    private $conn;
    private $table_cards = "cards";
    private $table_user_cards = "user_cards";
    private $table_decks = "decks";
    private $table_deck_cards = "deck_cards";

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    // Pobranie wszystkich definicji kart
    public function getAllCards() {
        $query = "SELECT * FROM " . $this->table_cards;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Pobranie kolekcji kart użytkownika
    public function getUserCollection($user_id) {
        $query = "SELECT c.id, c.name, c.description, c.type, c.rarity, c.cost, c.attack, c.health, c.image_url, uc.quantity
                  FROM " . $this->table_user_cards . " uc
                  JOIN " . $this->table_cards . " c ON uc.card_id = c.id
                  WHERE uc.user_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        return $stmt;
    }

    // Stworzenie nowej talii
    public function createDeck($user_id, $name) {
        $query = "INSERT INTO " . $this->table_decks . " SET user_id = :user_id, name = :name";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":name", $name);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Dodanie kart do talii
    public function addCardsToDeck($deck_id, $cards) {
        // $cards to tablica asocjacyjna, np. [['card_id' => 1, 'quantity' => 2], ...]
        $query = "INSERT INTO " . $this->table_deck_cards . " (deck_id, card_id, quantity) VALUES (:deck_id, :card_id, :quantity)";
        $stmt = $this->conn->prepare($query);

        foreach ($cards as $card) {
            $stmt->bindParam(":deck_id", $deck_id);
            $stmt->bindParam(":card_id", $card['card_id']);
            $stmt->bindParam(":quantity", $card['quantity']);
            if (!$stmt->execute()) {
                // Prosta obsługa błędu, w rzeczywistej aplikacji lepsza transakcyjność
                return false;
            }
        }
        return true;
    }

    // Pobranie talii użytkownika
    public function getDeck($deck_id) {
        $query = "SELECT c.id, c.name, dc.quantity
                  FROM " . $this->table_deck_cards . " dc
                  JOIN " . $this->table_cards . " c ON dc.card_id = c.id
                  WHERE dc.deck_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $deck_id);
        $stmt->execute();
        return $stmt;
    }
}
