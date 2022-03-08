<?php

// v1   10.11.2021
// Powered by M-Soft
// https://t.me/mufik

ini_set('max_execution_time', '1700');
set_time_limit(1700);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Content-Type: application/json; charset=utf-8');

http_response_code(200);

//--------------

include('functions.php');

if ($input["url"] == NULL) {
    $result["state"] = false;
    $result["message"]["url"] = "url is missing";
    http_response_code(422);
    echo json_encode($result);
    $log["input"] = $input;
    $log["result"] = $result;
    send_forward(json_encode($log), $log_url."?file=line");
    exit;
}

if (stripos($input["url"], "https://api.pipelinecrm.com/api/v3/") !== false) {
    if ($input["type"] == NULL || $input["type"] == "GET") {
        $input["data"]["app_key"] = $app_key;
        $input["data"]["api_key"] = $api_key;
        $result = json_decode(send_request($input["url"]."?".http_build_query($input["data"])), true);
    } else {
        $auth["app_key"] = $app_key;
        $auth["api_key"] = $api_key;
        $result = json_decode(send_request($input["url"]."?".http_build_query($auth), $input["type"], $input["data"]), true);
    }
} else {
    if ($input["type"] == NULL || $input["type"] == "GET") {
        $input["data"]["app_key"] = $app_key;
        $input["data"]["api_key"] = $api_key;
        $result = json_decode(send_request("https://api.pipelinecrm.com/api/v3/".$input["url"]."?".http_build_query($input["data"])), true);
    } else {
        $auth["app_key"] = $app_key;
        $auth["api_key"] = $api_key;
        $result = json_decode(send_request("https://api.pipelinecrm.com/api/v3/".$input["url"]."?".http_build_query($auth), $input["type"], $input["data"]), true);
    }
}

$log["input"] = $input;
$log["result"] = $result;
echo json_encode($result);


