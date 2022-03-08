<?php

// v1   08.03.2022
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

if ($input["name"] == NULL && $input["action"] != "delete" && $input["action"] != "get") {
    $result["state"] = false;
    $result["message"]["name"] = "name is missing";
}
if ($result["state"] === false) {
    http_response_code(422);
    echo json_encode($result);
    exit;
}

// Подготовка данных задачи
if (stripos($input["type"], "event") !== false) {
    $taskData["type"] = "CalendarEvent";
    if ($input["start"] != NULL) {
        $taskData["start_time"] = date("Y-m-d", strtotime($input["start"]))."T".date("H:i:s", strtotime($input["start"])).".000Z";
    }
    if ($input["end"] != NULL) {
        $taskData["end_time"] = date("Y-m-d", strtotime($input["end"]))."T".date("H:i:s", strtotime($input["end"])).".000Z";
    }
} else if (stripos($input["type"], "task") !== false) {
    $taskData["type"] = "CalendarTask";
    if ($input["date"] != NULL) {
        $taskData["due_date"] = date("Y-m-d", strtotime($input["date"]))."T".date("H:i:s", strtotime($input["date"])).".000Z";
    }
}
if ($input["category"] != NULL) {
    $auth["app_key"] = $app_key;
    $auth["api_key"] = $api_key;
    $getCategories = json_decode(send_request("https://api.pipelinecrm.com/api/v3/admin/event_categories?".http_build_query($auth)), true);
    if (is_array($getCategory["entries"]) === true) {
        foreach ($getCategory["entries"] as $oneCategory) {
            if ($oneCategory["name"] == $input["category"]) {
                $taskData["category_id"] = $oneCategory["id"];
                break;
            }
        }
    }
}
if ($input["name"] != NULL) {
    $taskData["name"] = $input["name"];
}
if ($input["description"] != NULL) {
    $taskData["description"] = $input["description"];
}
if ($input["complete"] === true || $input["complete"] === "true" || $input["complete"] === 1 || $input["complete"] === "1") {
    $taskData["complete"] = true;
}
if ($input["active"] === false || $input["active"] === "false" || $input["active"] === 0 || $input["active"] === "0") {
    $taskData["active"] = false;
}
if ($input["priority"] != NULL) {
    $getPriority = json_decode(send_request("https://api.pipelinecrm.com/api/v3/admin/calendar_entry_priorities?".http_build_query($auth)), true);
    if (is_array($getPriority["entries"]) === true) {
        foreach ($getPriority["entries"] as $onePriority) {
            if ($onePriority["name"] == $input["priority"]) {
                $taskData["calendar_entry_priority_id"] = $onePriority["id"];
                break;
            }
        }
    }
}

if ($input["userId"] != NULL) {
    if (file_exists('users.json') === true) {
        $users = json_decode(file_get_contents('users.json'), true);
    } else {
        $result["state"] = false;
        $result["message"]["userId"] = "user not found. Please, create user";
    }
    if ($users[$input["userId"]] != NULL) {
        $taskData["association_id"] = $users[$input["userId"]];
        $taskData["association_type"] = "Person";
    } else {
        $result["state"] = false;
        $result["message"]["userId"] = "user not found. Please, create user";
    }
    if ($result["state"] === false) {
        http_response_code(422);
        echo json_encode($result);
        exit;
    }
} else if ($input["dealId"] != NULL) {
    $taskData["association_id"] = $input["dealId"];
    $taskData["association_type"] = "Deal";
    settype($taskData["entity_id"], "int");
} else if ($input["contactId"] != NULL) {
    $taskData["association_id"] = $input["contactId"];
    $taskData["association_type"] = "Person";
    settype($taskData["entity_id"], "int");
}


// Создание задачи
$tasksData["calendar_entry"] = $taskData;
if ($input["calendarId"] != NULL) {
    if ($input["action"] == "delete") {
        $createTask = json_decode(send_request("https://api.pipelinecrm.com/api/v3/calendar_entries/".$input["calendarId"]."?".http_build_query($auth), "DELETE"), true);
    } else if ($input["action"] == "get") {
        $createTask = json_decode(send_request("https://api.pipelinecrm.com/api/v3/calendar_entries/".$input["calendarId"]."?".http_build_query($auth)), true);
    } else {
        $createTask = json_decode(send_request("https://api.pipelinecrm.com/api/v3/calendar_entries/".$input["calendarId"]."?".http_build_query($auth), "PUT", $tasksData), true);
    }
} else {
    $createTask = json_decode(send_request("https://api.pipelinecrm.com/api/v3/calendar_entries?".http_build_query($auth), "POST", $tasksData), true);
}
$result["send"] = $taskData;
$result["result"] = $createTask;
echo json_encode($result);




