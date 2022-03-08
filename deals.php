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
        $dealData["primary_contact_id"] = $users[$input["userId"]];
    } else {
        $result["state"] = false;
        $result["message"]["userId"] = "user not found. Please, create user";
    }
}
if ($input["name"] == NULL) {
    $result["state"] = false;
    $result["message"]["name"] = "name is missing";
}
if ($result["state"] === false) {
    http_response_code(422);
    echo json_encode($result);
    exit;
}

// Подготовка данных сделки
$dealData["name"] = $input["name"];
if ($input["note"] != NULL) {
    $dealData["summary"] = $input["note"];
}
if ($input["summary"] != NULL) {
    $dealData["summary"] = $input["summary"];
}
if ($input["status"] != NULL) {
    $auth["app_key"] = $app_key;
    $auth["api_key"] = $api_key;
    $dealStatus = json_decode(send_request("https://api.pipelinecrm.com/api/v3/admin/deal_statuses?".http_build_query($auth)), true);
    if (is_array($dealStatus["entries"]) === true) {
        foreach ($dealStatus["entries"] as $oneDealStatus) {
            $allDealStatus[$oneDealStatus["name"]] = $oneDealStatus["id"];
        }
    }
    if ($allDealStatus[$input["status"]] != NULL) {
        $dealData["status"] = $allDealStatus[$input["status"]];
    }
}
if ($input["expected_close_date"] != NULL) {
    $dealData["expected_close_date"] = date("Y-m-d", strtotime($input["expected_close_date"]));
}
if ($input["closed_time"] != NULL) {
    $dealData["closed_time"] = date("Y-m-d", strtotime($input["closed_time"]));
}
if ($input["is_archived"] === true || $input["is_archived"] === "true" || $input["is_archived"] === 1 || $input["is_archived"] === "1") {
    $dealData["is_archived"] = true;
}
if ($input["value"] != NULL) {
    $dealData["value"] = str_replace(" ", "", str_replace(",", ".", $input["value"]));
    settype($dealData["value"], "int");
}
if ($input["probability"] != NULL) {
    $input["probability"] = str_replace(" ", "", str_replace(",", ".", $input["probability"]));
    settype($input["probability"], "int");
    if ($input["probability"] >= 0 && $input["probability"] <= 100) {
        $dealData["probability"] = $input["probability"];
    }
}
if ($input["stage"] != NULL) {
    $auth["app_key"] = $app_key;
    $auth["api_key"] = $api_key;
    $dealStage = json_decode(send_request("https://api.pipelinecrm.com/api/v3/admin/deal_stages?".http_build_query($auth)), true);
    if (is_array($dealStage["entries"]) === true) {
        foreach ($dealStage["entries"] as $oneDealStage) {
            $allDealStage[$oneDealStage["name"]] = $oneDealStage["id"];
        }
    }
    if ($allDealStage[$input["stage"]] != NULL) {
        $dealData["deal_stage_id"] = $allDealStage[$input["stage"]];
    }
}
if ($input["loss_reason"] != NULL) {
    $auth["app_key"] = $app_key;
    $auth["api_key"] = $api_key;
    $dealLossReason = json_decode(send_request("https://api.pipelinecrm.com/api/v3/admin/deal_loss_reasons?".http_build_query($auth)), true);
    if (is_array($dealLossReason["entries"]) === true) {
        foreach ($dealLossReason["entries"] as $oneDealLossReason) {
            $allDealLossReason[$oneDealLossReason["name"]] = $oneDealLossReason["id"];
        }
    }
    if ($allDealLossReason[$input["loss_reason"]] != NULL) {
        $dealData["deal_loss_reason_id"] = $allDealLossReason[$input["loss_reason"]];
    }
}
if ($input["loss_reason_note"] != NULL) {
    $dealData["deal_loss_reason_notes"] = $input["loss_reason_note"];
}
if ($input["source"] != NULL) {
    $auth["app_key"] = $app_key;
    $auth["api_key"] = $api_key;
    $dealSource = json_decode(send_request("https://api.pipelinecrm.com/api/v3/admin/lead_sources?".http_build_query($auth)), true);
    if (is_array($dealSource["entries"]) === true) {
        foreach ($dealSource["entries"] as $oneDealSource) {
            $allDealSource[$oneDealSource["name"]] = $oneDealSource["id"];
        }
    }
    if ($allDealSource[$input["source"]] != NULL) {
        $dealData["source_id"] = $allDealSource[$input["source"]];
    }
}

if (is_array($input["fields"]) === true) {
    $auth["app_key"] = $app_key;
    $auth["api_key"] = $api_key;
    $dealFields = json_decode(send_request("https://api.pipelinecrm.com/api/v3/admin/deal_custom_field_labels?".http_build_query($auth)), true);
    if (is_array($dealFields["entries"]) === true) {
        foreach ($dealFields["entries"] as $oneDealFields) {
            $dealFields[$oneDealFields["name"]] = $oneDealFields["id"];
            $dealFieldsType[$oneDealFields["name"]] = $oneDealFields["field_type"];
        }
    }
    foreach ($input["fields"] as $fieldsKey => $fieldsValue) {
        if ($dealFields[$fieldsKey] != NULL) {
            if ($dealFieldsType[$fieldsKey] == "numeric") {
                $dealData["custom_fields"]["custom_label_".$dealFields[$fieldsKey]] = str_replace(" ", "", str_replace(",", ".", $fieldsValue));
                settype($dealData["custom_fields"]["custom_label_".$dealFields[$fieldsKey]], "float");
            } else if ($dealFieldsType[$fieldsKey] == "date") {
                $dealData["custom_fields"]["custom_label_".$dealFields[$fieldsKey]] = date("Y-m-d", strtotime($fieldsValue));
            } else if ($dealFieldsType[$fieldsKey] == "multi_select" && is_array($fieldsValue)) {
                foreach ($fieldsValue as $oneFieldsValue) {
                    $dealData["custom_fields"]["custom_label_".$dealFields[$fieldsKey]][] = $oneFieldsValue;
                }
            } else if ($dealFieldsType[$fieldsKey] == "boolean") {
                if ($fieldsValue != NULL && $fieldsValue != false && $fieldsValue != "false") {
                    $dealData["custom_fields"]["custom_label_".$dealFields[$fieldsKey]][] = true;
                } else {
                    $dealData["custom_fields"]["custom_label_".$dealFields[$fieldsKey]][] = false;
                }
            } else {
                $dealData["custom_fields"]["custom_label_".$dealFields[$fieldsKey]] = $fieldsValue;
            }
            if ($dealData["custom_fields"]["custom_label_".$dealFields[$fieldsKey]] == "") {
                $dealData["custom_fields"]["custom_label_".$dealFields[$fieldsKey]] = null;
            }
        }
    }
}



// Создание/обновление сделки
$dealsData["deal"] = $dealData;
$auth["app_key"] = $app_key;
$auth["api_key"] = $api_key;
if ($input["dealId"] != NULL) {
    $updateDeal = json_decode(send_request("https://api.pipelinecrm.com/api/v3/deals/".$input["dealId"]."?".http_build_query($auth), "PUT", $dealsData), true);
    $result["result"] = $updateDeal;
    $result["send"] = $dealData;
} else {
    $createDeal = json_decode(send_request("https://api.pipelinecrm.com/api/v3/deals?".http_build_query($auth), "POST", $dealsData), true);
    $result["result"] = $createDeal;
    $result["send"] = $dealsData;
}

echo json_encode($result);



