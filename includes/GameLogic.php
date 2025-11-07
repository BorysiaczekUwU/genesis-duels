<?php

class GameLogic {
    private $conn;
    private $table_game_sessions = "game_sessions";

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    // Prosty matchmaking - na razie losowy lub PvE
    public function findMatch($player1_id, $deck_id, $game_type = 'PvP') {
        if ($game_type === 'PvE') {
            // Player2_id = NULL dla AI
            return $this->createGameSession($player1_id, NULL, $deck_id, NULL, 'PvE');
        }

        // TODO: Zaimplementować logikę szukania prawdziwego gracza
        // Na razie symulujemy znalezienie gracza AI jako placeholder
        return $this->createGameSession($player1_id, NULL, $deck_id, NULL, 'PvE', 0);
    }

    // Stworzenie sesji gry
    private function createGameSession($p1_id, $p2_id, $p1_deck, $p2_deck, $type, $bet = 0) {
        $query = "INSERT INTO " . $this->table_game_sessions . "
                  SET player1_id=:p1_id, player2_id=:p2_id, player1_deck_id=:p1_deck,
                      player2_deck_id=:p2_deck, current_turn=:current_turn,
                      game_type=:type, bet_amount=:bet";

        $stmt = $this->conn->prepare($query);

        // Losowo wybierz, kto zaczyna
        $current_turn = rand(0, 1) ? $p1_id : $p2_id;
        // Jeśli p2 jest AI, p1 zawsze zaczyna
        if ($p2_id === NULL) {
            $current_turn = $p1_id;
        }

        $stmt->bindParam(":p1_id", $p1_id);
        $stmt->bindParam(":p2_id", $p2_id);
        $stmt->bindParam(":p1_deck", $p1_deck);
        $stmt->bindParam(":p2_deck", $p2_deck);
        $stmt->bindParam(":current_turn", $current_turn);
        $stmt->bindParam(":type", $type);
        $stmt->bindParam(":bet", $bet);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Pobranie stanu gry
    public function getGameState($session_id) {
        $query = "SELECT * FROM " . $this->table_game_sessions . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $session_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Logika tury (bardzo uproszczona)
    public function playTurn($session_id, $player_id, $action_data) {
        $gameState = $this->getGameState($session_id);

        // 1. Walidacja: czyja tura?
        if ($gameState['current_turn'] != $player_id) {
            return array("success" => false, "message" => "Not your turn.");
        }

        // 2. Przetwarzanie akcji (np. zagranie karty, atak)
        // TODO: Zaimplementować szczegółową logikę akcji
        // Na razie tylko zmieniamy turę
        $next_turn_player_id = ($player_id == $gameState['player1_id']) ? $gameState['player2_id'] : $gameState['player1_id'];

        // Jeśli gramy z AI, tura AI jest obsługiwana osobno
        if ($gameState['player2_id'] === NULL) {
             // TODO: Logika AI
             $next_turn_player_id = $gameState['player1_id']; // Wracamy do gracza po "turze" AI
        }


        // 3. Aktualizacja stanu gry w bazie
        $query = "UPDATE " . $this->table_game_sessions . " SET current_turn = :next_turn WHERE id = :session_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":next_turn", $next_turn_player_id);
        $stmt->bindParam(":session_id", $session_id);

        if ($stmt->execute()) {
             return array("success" => true, "message" => "Turn ended.", "newState" => $this->getGameState($session_id));
        }

        return array("success" => false, "message" => "Failed to update game state.");
    }
}
