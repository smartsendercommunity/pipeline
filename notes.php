<?php

// v1   07.03.2022
// Powered by Smart Sender
// https://smartsender.com

ini_set('max_execution_time', '1700');
set_time_limit(1700);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Content-Type: application/json; charset=utf-8');

http_response_code(200);

//--------------

include('functions.php');

// Проверка наличия всех обезательных полей
if ($input["userId"] == NULL) {
    $result["state"] = false;
    $result["message"]["userId"] = "userId is missing";
} else {
    if (file_exists('users.json') === true) {
        $users = json_decode(file_get_contents('users.json'), true);
    }
    if ($users[$input["userId"]] != NULL) {
        $userId = $users[$input["userId"]];
    } else {
        $result["state"] = false;
        $result["message"]["userId"] = "user not found. Please, create user";
    }
}
if ($result["state"] === false) {
    http_response_code(422);
    echo json_encode($result);
    exit;
}

// Подготовка и отправка примечания
if ($input["title"] != NULL) {
    $note["title"] = $input["title"];
}
if ($input["content"] != NULL) {
    $note["content"] = $input["content"];
}
if ($input["dealId"] != NULL) {
    $note["deal_id"] = $input["dealId"];
}
$note["person_id"] = $userId;
if ($input["category"] != NULL) {
    $auth["app_key"] = $app_key;
    $auth["api_key"] = $api_key;
    $getCategory = json_decode(send_request("https://api.pipelinecrm.com/api/v3/admin/note_categories?".http_build_query($auth)), true);
    if (is_array($getCategory["entries"]) === true) {
        foreach ($getCategory["entries"] as $oneCategory) {
            if ($oneCategory["name"] == $input["category"]) {
                $note["note_category_id"] = $oneCategory["id"];
                break;
            }
        }
    }
}
$notes["note"] = $note;
$auth["app_key"] = $app_key;
$auth["api_key"] = $api_key;
if ($input["noteId"] != NULL) {
    if ($input["action"] == "delete") {
        $addNotes = json_decode(send_request("https://api.pipelinecrm.com/api/v3/notes/".$input["noteId"]."?".http_build_query($auth), "DELETE"), true);
    } else if ($input["action"] == "get") {
        $addNotes = json_decode(send_request("https://api.pipelinecrm.com/api/v3/notes/".$input["noteId"]."?".http_build_query($auth)), true);
    } else {
        $addNotes = json_decode(send_request("https://api.pipelinecrm.com/api/v3/notes/".$input["noteId"]."?".http_build_query($auth), "PUT", $notes), true);
    }
} else {
    if ($input["action"] == "get") {
        $auth["person_id"] = $userId;
        $auth["deal_id"] = $input["dealId"];
        $addNotes = json_decode(send_request("https://api.pipelinecrm.com/api/v3/notes?".http_build_query($auth)), true);
    } else {
        $addNotes = json_decode(send_request("https://api.pipelinecrm.com/api/v3/notes?".http_build_query($auth), "POST", $notes), true);
    }
}
$result = $addNotes;


echo json_encode($result);