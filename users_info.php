<?php

// v1   06.03.2022
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

$auth["app_key"] = $app_key;
$auth["api_key"] = $api_key;
$getContacts = json_decode(send_request("https://api.pipelinecrm.com/api/v3/people/".$userId."?".http_build_query($auth)), true);
$getContactFields = json_decode(send_request("https://api.pipelinecrm.com/api/v3/admin/person_custom_field_labels?".http_build_query($auth)), true);
if (is_array($getContactFields["entries"]) === true) {
    foreach ($getContactFields["entries"] as $oneContactField) {
        $contactField["custom_label_".$oneContactField["id"]] = $oneContactField["name"];
    }
}
if (is_array($getContacts["custom_fields"]) === true) {
    foreach ($getContacts["custom_fields"] as $oneContactKeys => $oneContactVariables) {
        $getContacts["variables"][$contactField[$oneContactKeys]] = $oneContactVariables;
    }
}
$result["user"] = $getContacts;
echo json_encode($result);




