<?php
require_once '../utils/utils.php';
require_once '../connect.php';
require_once '../error_codes.php';
require_once '../models/user.php';
require_once '../controllers/user_controller.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");


if($_SERVER["REQUEST_METHOD"] == "POST") {

    $db = Connectivity::Connect();

    if($db != null) {

        $data = file_get_contents("php://input");
        list($_, $token) = explode('=', $data);

        try {

            $payload = Utils::verify_google_token($token);

            if ($payload) {

                $user = new User();
                $user->setName($payload["name"]);
                $user->setPicture($payload["picture"]);
                $user->setLocale($payload["locale"]);
                $user->setEmail($payload["email"]);
                $user_controller = new UserController($db);

                try {

                    $user_id = $user_controller->get_user_id_by_email($user)->fetch(PDO::FETCH_ASSOC);

                    if ($user_id == null) {
                        $last_user_id = $user_controller->create($user);
                        $last_user_id_arr = array('id' => $last_user_id);
                        echo json_encode($last_user_id_arr += $payload += ErrorCodes::no_error());
                    } else {
                        $user_controller->update_user_sign_in_time($user_id['id']);
                        echo json_encode($user_id += $payload += ErrorCodes::no_error());
                    }

                    Utils::create_dir($user->getEmail());

                } catch (Exception $ex) {
                    echo json_encode(ErrorCodes::db_mysql_exception($ex->getMessage()));
                }

            } else {
                echo json_encode(ErrorCodes::token_invalid_error());
            }
        } catch (Exception $e) {
            echo json_encode(ErrorCodes::token_invalid_format_error($e->getMessage()));
        }
    } else {
        echo json_encode(ErrorCodes::db_connection_error());
    }
}