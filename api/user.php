<?php
// Nagłówki wymagane do obsługi API
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../includes/User.php';

$user = new User();

// Pobieranie danych wysłanych w żądaniu
$data = json_decode(file_get_contents("php://input"));

// Prosty routing na podstawie parametru 'action'
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'register':
        if (!empty($data->username) && !empty($data->email) && !empty($data->password)) {
            $user->username = $data->username;
            $user->email = $data->email;
            $user->password = $data->password;

            if ($user->emailExists()) {
                http_response_code(400);
                echo json_encode(array("message" => "Email already exists."));
                return;
            }

            if ($user->register()) {
                http_response_code(201);
                echo json_encode(array("message" => "User was successfully registered."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to register the user."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Incomplete data."));
        }
        break;

    case 'login':
        if (!empty($data->email) && !empty($data->password)) {
            $user->email = $data->email;
            $user->password = $data->password;

            if ($user->login()) {
                // W przyszłości tutaj generowanie tokenu JWT
                http_response_code(200);
                echo json_encode(array(
                    "message" => "Successful login.",
                    "user_id" => $user->id,
                    "username" => $user->username
                ));
            } else {
                http_response_code(401);
                echo json_encode(array("message" => "Login failed."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Incomplete data."));
        }
        break;

    case 'profile':
        $user_id = isset($_GET['id']) ? $_GET['id'] : die();
        $stmt = $user->getProfile($user_id);
        $num = $stmt->rowCount();

        if ($num > 0) {
            $user_arr = array();
            $user_arr["records"] = array();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $user_item = array(
                    "id" => $id,
                    "username" => $username,
                    "level" => $level,
                    "experience" => $experience,
                    "credits" => $credits,
                    "shards" => $shards,
                    "avatar" => $avatar
                );
                array_push($user_arr["records"], $user_item);
            }
            http_response_code(200);
            echo json_encode($user_arr);
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "User not found."));
        }
        break;

    default:
        http_response_code(404);
        echo json_encode(array("message" => "Invalid action."));
        break;
}
