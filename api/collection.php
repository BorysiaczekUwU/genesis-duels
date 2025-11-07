<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");

require_once '../includes/Card.php';
require_once '../config/database.php';

$card = new Card();
$data = json_decode(file_get_contents("php://input"));
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'get_collection':
        $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : die();
        $stmt = $card->getUserCollection($user_id);
        $num = $stmt->rowCount();

        if ($num > 0) {
            $cards_arr = array("records" => array());
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $card_item = array(
                    "id" => $id, "name" => $name, "description" => $description, "type" => $type,
                    "rarity" => $rarity, "cost" => $cost, "attack" => $attack, "health" => $health,
                    "image_url" => $image_url, "quantity" => $quantity
                );
                array_push($cards_arr["records"], $card_item);
            }
            http_response_code(200);
            echo json_encode($cards_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No cards found."));
        }
        break;

    case 'create_deck':
        if (!empty($data->user_id) && !empty($data->name) && !empty($data->cards)) {
            $deck_id = $card->createDeck($data->user_id, $data->name);
            if ($deck_id && $card->addCardsToDeck($deck_id, (array)$data->cards)) {
                http_response_code(201);
                echo json_encode(array("message" => "Deck created successfully.", "deck_id" => $deck_id));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create deck."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Incomplete data for deck creation."));
        }
        break;

    case 'get_deck':
        $deck_id = isset($_GET['deck_id']) ? $_GET['deck_id'] : die();
        $stmt = $card->getDeck($deck_id);
        $num = $stmt->rowCount();

        if ($num > 0) {
            $deck_arr = array("cards" => array());
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $card_item = array("id" => $id, "name" => $name, "quantity" => $quantity);
                array_push($deck_arr["cards"], $card_item);
            }
            http_response_code(200);
            echo json_encode($deck_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Deck not found."));
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(array("message" => "Invalid action."));
        break;
}
