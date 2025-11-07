<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");

require_once '../includes/GameLogic.php';
require_once '../config/database.php';

$game = new GameLogic();
$data = json_decode(file_get_contents("php://input"));
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'find_match':
        if (!empty($data->player_id) && !empty($data->deck_id)) {
            $game_type = isset($data->game_type) ? $data->game_type : 'PvE'; // Domyślnie PvE
            $session_id = $game->findMatch($data->player_id, $data->deck_id, $game_type);

            if ($session_id) {
                http_response_code(200);
                echo json_encode(array("message" => "Match found.", "session_id" => $session_id, "gameState" => $game->getGameState($session_id)));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to find a match."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Incomplete data for matchmaking."));
        }
        break;

    case 'get_state':
        $session_id = isset($_GET['session_id']) ? $_GET['session_id'] : die();
        $gameState = $game->getGameState($session_id);
        if ($gameState) {
            http_response_code(200);
            echo json_encode($gameState);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Game session not found."));
        }
        break;

    case 'play_turn':
        if (!empty($data->session_id) && !empty($data->player_id) && isset($data->action_data)) {
            $result = $game->playTurn($data->session_id, $data->player_id, $data->action_data);
            if ($result['success']) {
                http_response_code(200);
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Incomplete data for playing a turn."));
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(array("message" => "Invalid action."));
        break;
}
