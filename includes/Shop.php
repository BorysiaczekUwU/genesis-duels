<?php

class Shop {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    // Otwieranie skrzynki (logika losowania)
    public function openLootbox($user_id) {
        // 1. Zdefiniuj możliwe nagrody i ich wagi (szanse)
        // Uproszczony przykład: 70% Common, 20% Rare, 8% Epic, 2% Legendary
        $rarity_chances = [
            'Common' => 70,
            'Rare' => 20,
            'Epic' => 8,
            'Legendary' => 2,
        ];

        $rewards = [];
        $cards_in_box = 5; // Każda skrzynka daje 5 kart

        for ($i = 0; $i < $cards_in_box; $i++) {
            $rand = mt_rand(1, 100);
            $current_chance = 0;
            $chosen_rarity = 'Common';

            foreach ($rarity_chances as $rarity => $chance) {
                $current_chance += $chance;
                if ($rand <= $current_chance) {
                    $chosen_rarity = $rarity;
                    break;
                }
            }

            // 2. Wylosuj kartę o wybranej rzadkości
            $card = $this->getRandomCardByRarity($chosen_rarity);
            if ($card) {
                $rewards[] = $card;
            }
        }

        // 3. Dodaj wylosowane karty do kolekcji gracza
        foreach ($rewards as $reward_card) {
            $this->addCardToUserCollection($user_id, $reward_card['id']);
        }

        return $rewards;
    }

    // Prywatna metoda do losowania karty
    private function getRandomCardByRarity($rarity) {
        $query = "SELECT id, name FROM cards WHERE rarity = :rarity ORDER BY RAND() LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':rarity', $rarity);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Prywatna metoda do dodawania karty do kolekcji
    private function addCardToUserCollection($user_id, $card_id) {
        // Sprawdź, czy użytkownik już ma tę kartę
        $query_check = "SELECT id, quantity FROM user_cards WHERE user_id = :user_id AND card_id = :card_id";
        $stmt_check = $this->conn->prepare($query_check);
        $stmt_check->bindParam(':user_id', $user_id);
        $stmt_check->bindParam(':card_id', $card_id);
        $stmt_check->execute();

        if ($stmt_check->rowCount() > 0) {
            // Zaktualizuj ilość
            $row = $stmt_check->fetch(PDO::FETCH_ASSOC);
            $new_quantity = $row['quantity'] + 1;
            $query_update = "UPDATE user_cards SET quantity = :quantity WHERE id = :id";
            $stmt_update = $this->conn->prepare($query_update);
            $stmt_update->bindParam(':quantity', $new_quantity);
            $stmt_update->bindParam(':id', $row['id']);
            $stmt_update->execute();
        } else {
            // Dodaj nową kartę
            $query_insert = "INSERT INTO user_cards (user_id, card_id, quantity) VALUES (:user_id, :card_id, 1)";
            $stmt_insert = $this->conn->prepare($query_insert);
            $stmt_insert->bindParam(':user_id', $user_id);
            $stmt_insert->bindParam(':card_id', $card_id);
            $stmt_insert->execute();
        }
    }

    // Logika hazardu - Double or Nothing
    public function playDoubleOrNothing($user_id, $credits_risked) {
        $user_credits = $this->getUserCredits($user_id);
        if ($user_credits < $credits_risked) {
            return ["success" => false, "message" => "Not enough credits."];
        }

        // Prosta losowość 50/50
        $won = mt_rand(0, 1) === 1;

        if ($won) {
            $this->updateUserCredits($user_id, $credits_risked); // Wygrywa, więc dostaje podwojoną stawkę (netto: +stawka)
            return ["success" => true, "won" => true, "new_credits" => $this->getUserCredits($user_id)];
        } else {
            $this->updateUserCredits($user_id, -$credits_risked); // Przegrywa, traci stawkę
            return ["success" => true, "won" => false, "new_credits" => $this->getUserCredits($user_id)];
        }
    }

    // Metody pomocnicze do zarządzania walutą
    public function getUserCredits($user_id) {
        $query = "SELECT credits FROM users WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['credits'] : 0;
    }

    public function updateUserCredits($user_id, $amount) {
        $query = "UPDATE users SET credits = credits + :amount WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':amount', $amount, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
