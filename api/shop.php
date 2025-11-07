<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once '../includes/Shop.php';
require_once '../config/database.php';

$shop = new Shop();
$data = json_decode(file_get_contents("php://input"));
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'open_lootbox':
        if (!empty($data->user_id)) {
            // W pełnej wersji: sprawdź, czy użytkownik ma wystarczająco waluty i ją pobierz
            // $shop->updateUserCredits($data->user_id, -100); // Przykładowy koszt

            $rewards = $shop->openLootbox($data->user_id);
            if ($rewards) {
                http_response_code(200);
                echo json_encode(array("message" => "Lootbox opened.", "rewards" => $rewards));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Could not open lootbox."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "User ID not provided."));
        }
        break;

    case 'double_or_nothing':
        if (!empty($data->user_id) && !empty($data->credits_risked)) {
            $result = $shop->playDoubleOrNothing($data->user_id, $data->credits_risked);
            if ($result['success']) {
                http_response_code(200);
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Incomplete data for Double or Nothing."));
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(array("message" => "Invalid action."));
        break;
}
