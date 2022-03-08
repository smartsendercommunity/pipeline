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
    if ($input["contactId"] == NULL) {
        if ($input["dealId"] == NULL) {
            $result["state"] = false;
            $result["message"]["userId"] = "userId or dealId is missing";
        }
    } else {
        $userId = $input["contactId"];
    }
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
$getDealFields = json_decode(send_request("https://api.pipelinecrm.com/api/v3/admin/deal_custom_field_labels?".http_build_query($auth)), true);
if (is_array($getDealFields["entries"]) === true) {
    foreach ($getDealFields["entries"] as $oneDealField) {
        $dealField["custom_label_".$oneDealField["id"]] = $oneDealField["name"];
    }
}

if ($input["dealId"] != NULL) {
    $getDeals = json_decode(send_request("https://api.pipelinecrm.com/api/v3/deals/".$input["dealId"]."?".http_build_query($auth)), true);
    if (is_array($getDeals["custom_fields"]) === true) {
        foreach ($getDeals["custom_fields"] as $oneDealKey => $oneDealVariables) {
            $getDeals["variables"][$dealField[$oneDealKey]] = $oneDealVariables;
        }
    }
    $result["deals"] = $getDeals;
} else if ($userId != NULL) {
    $getContacts = json_decode(send_request("https://api.pipelinecrm.com/api/v3/people/".$userId."?".http_build_query($auth)), true);
    if (is_array($getContacts["deal_ids"]) === true) {
        foreach ($getContacts["deal_ids"] as $oneDeal) {
            $getDeals = json_decode(send_request("https://api.pipelinecrm.com/api/v3/deals/".$oneDeal."?".http_build_query($auth)), true);
            if (is_array($getDeals["custom_fields"]) === true) {
                foreach ($getDeals["custom_fields"] as $oneDealKey => $oneDealVariables) {
                    $getDeals["variables"][$dealField[$oneDealKey]] = $oneDealVariables;
                }
            }
            $result["deals"][] = $getDeals;
        }
    }
}

echo json_encode($result);




