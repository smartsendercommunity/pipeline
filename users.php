<?php

// v1   05.03.2022
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
}
if ($input["fullName"] == NULL && $input["firstName"] == NULL && $input["lastName"] == NULL) {
    $result["state"] = false;
    $result["message"]["name"] = "one of the three fields (fullName, firstName, lastName) is missing";
}
if ($result["state"] === false) {
    http_response_code(422);
    echo json_encode($result);
    exit;
}

// Подготовка данных контакта
if (file_exists('users.json') === true) {
    $users = json_decode(file_get_contents('users.json'), true);
}
if ($users[$input["userId"]] != NULL) {
    $userId = $users[$input["userId"]];
} else if ($input["contactId"] != NULL) {
    $auth["app_key"] = $app_key;
    $auth["api_key"] = $api_key;
    $getContacts = json_decode(send_request("https://api.pipelinecrm.com/api/v3/people/".$input["contactId"]."?".http_build_query($auth)), true);
    if (stripos($getContacts["instant_message"], "ssId_") === false) {
        $userId = $input["contactId"];
        $users[$input["userId"]] = $userId;
        file_put_contents("users.json", json_encode($users));
    }
}
if ($input["fullName"] != NULL) {
    $userData["full_name"] = $input["fullName"];
}
if ($input["firstName"] != NULL) {
    $userData["first_name"] = $input["firstName"];
}
if ($input["lastName"] != NULL) {
    $userData["last_name"] = $input["lastName"];
}
if ($input["note"] != NULL) {
    $userData["summary"] = $input["note"];
}
if ($input["summary"] != NULL) {
    $userData["summary"] = $input["summary"];
}
if ($input["home_phone"] != NULL) {
    $userData["home_phone"] = $input["home_phone"];
}
if ($input["mobile"] != NULL) {
    $userData["mobile"] = $input["mobile"];
}
if ($input["position"] != NULL) {
    $userData["position"] = $input["position"];
}
if ($input["website"] != NULL) {
    $userData["website"] = $input["website"];
}
if ($input["email2"] != NULL) {
    $userData["email2"] = $input["email2"];
}
if ($input["home_email"] != NULL) {
    $userData["home_email"] = $input["home_email"];
}
if ($input["company"] != NULL) {
    $userData["company_name"] = $input["company"];
}
if ($input["type"] == "Contact" || $input["type"] == "Lead") {
    $userData["type"] = $input["type"];
}
if ($input["phone"] != NULL) {
    $userData["phone"] = $input["phone"];
}
if ($input["email"] != NULL) {
    $userData["email"] = $input["email"];
}
if ($input["work_address_1"] != NULL) {
    $userData["work_address_1"] = $input["work_address_1"];
}
if ($input["work_address_2"] != NULL) {
    $userData["work_address_2"] = $input["work_address_2"];
}
if ($input["work_city"] != NULL) {
    $userData["work_city"] = $input["work_city"];
}
if ($input["work_state"] != NULL) {
    $userData["work_state"] = $input["work_state"];
}
if ($input["work_country"] != NULL) {
    $userData["work_country"] = $input["work_country"];
}
if ($input["work_postal_code"] != NULL) {
    $userData["work_postal_code"] = $input["work_postal_code"];
}
if ($input["home_address_1"] != NULL) {
    $userData["home_address_1"] = $input["home_address_1"];
}
if ($input["home_address_2"] != NULL) {
    $userData["home_address_2"] = $input["home_address_2"];
}
if ($input["home_city"] != NULL) {
    $userData["home_city"] = $input["home_city"];
}
if ($input["home_state"] != NULL) {
    $userData["home_state"] = $input["home_state"];
}
if ($input["home_country"] != NULL) {
    $userData["home_country"] = $input["home_country"];
}
if ($input["home_postal_code"] != NULL) {
    $userData["home_postal_code"] = $input["home_postal_code"];
}
if ($input["facebook_url"] != NULL) {
    $userData["facebook_url"] = $input["facebook_url"];
}
if ($input["linked_in_url"] != NULL) {
    $userData["linked_in_url"] = $input["linked_in_url"];
}
if ($input["twitter"] != NULL) {
    $userData["twitter"] = $input["twitter"];
}
if ($input["leadStatus"] != NULL) {
    $auth["app_key"] = $app_key;
    $auth["api_key"] = $api_key;
    $leadStatus = json_decode(send_request("https://api.pipelinecrm.com/api/v3/admin/lead_statuses?".http_build_query($auth)), true);
    if (is_array($leadStatus["entries"]) === true) {
        foreach ($leadStatus["entries"] as $oneLeadStatus) {
            if ($oneLeadStatus["name"] == $input["leadStatus"]) {
                $userData["lead_status_id"] = $oneLeadStatus["id"];
                break;
            }
        }
    }
}
if ($input["leadSource"] != NULL) {
    $auth["app_key"] = $app_key;
    $auth["api_key"] = $api_key;
    $leadSource = json_decode(send_request("https://api.pipelinecrm.com/api/v3/admin/lead_sources?".http_build_query($auth)), true);
    if (is_array($leadSource["entries"]) === true) {
        foreach ($leadSource["entries"] as $oneLeadSource) {
            if ($oneLeadSource["name"] == $input["leadSource"]) {
                $userData["lead_source_id"] = $oneLeadSource["id"];
                break;
            }
        }
    }
}
if ($input["photo"] != NULL) {
    $userData["image_thumb_url"] = $input["photo"];
}
if ($input["tags"] != NULL) {
    $auth["app_key"] = $app_key;
    $auth["api_key"] = $api_key;
    $allTags = json_decode(send_request("https://api.pipelinecrm.com/api/v3/admin/predefined_contacts_tags?".http_build_query($auth)), true);
    if (is_array($allTags["entries"]) === true) {
        foreach ($allTags["entries"] as $oneTags) {
            $tags[$oneTags["name"]] = $oneTags["id"];
        }
    }
    if (is_array($input["tags"]) === true) {
        foreach ($input["tags"] as $oneTag) {
            if ($tags[$oneTag] != NULL) {
                $userData["predefined_contacts_tag_ids"][] = $tags[$oneTag];
            }
        }
    } else {
        if ($tags[$input["tags"]] != NULL) {
            $userData["predefined_contacts_tag_ids"][] = $tags[$input["tags"]];
        }
    }
}
$userData["instant_message"] = "ssId_".$input["userId"];
/*if ($input["manager"] != NULL) {
    $amoManagers = json_decode(send_bearer($amo_url."/api/v4/users?limit=250", $access["token"]), true);
    if (is_array($amoManagers["_embedded"]["users"]) === true) {
        foreach ($amoManagers["_embedded"]["users"] as $oneManager) {
            if ($oneManager["email"] == $input["manager"]) {
                $userData["responsible_user_id"] = $oneManager["id"];
                break;
            }
        }
    }
}*/
if (is_array($input["fields"]) === true) {
    $auth["app_key"] = $app_key;
    $auth["api_key"] = $api_key;
    $contactFields = json_decode(send_request("https://api.pipelinecrm.com/api/v3/admin/person_custom_field_labels?".http_build_query($auth)), true);
    if (is_array($contactFields["entries"]) === true) {
        foreach ($contactFields["entries"] as $oneContactFields) {
            $contactFields[$oneContactFields["name"]] = $oneContactFields["id"];
            $contactFieldsType[$oneContactFields["name"]] = $oneContactFields["field_type"];
        }
    }
    foreach ($input["fields"] as $fieldsKey => $fieldsValue) {
        if ($contactFields[$fieldsKey] != NULL) {
            if ($contactFieldsType[$fieldsKey] == "numeric") {
                $userData["custom_fields"]["custom_label_".$contactFields[$fieldsKey]] = str_replace(" ", "", str_replace(",", ".", $fieldsValue));
                settype($userData["custom_fields"]["custom_label_".$contactFields[$fieldsKey]], "float");
            } else if ($contactFieldsType[$fieldsKey] == "date") {
                $userData["custom_fields"]["custom_label_".$contactFields[$fieldsKey]] = date("Y-m-d", strtotime($fieldsValue));
            } else if ($contactFieldsType[$fieldsKey] == "multi_select" && is_array($fieldsValue)) {
                foreach ($fieldsValue as $oneFieldsValue) {
                    $userData["custom_fields"]["custom_label_".$contactFields[$fieldsKey]][] = $oneFieldsValue;
                }
            } else if ($contactFieldsType[$fieldsKey] == "boolean") {
                if ($fieldsValue != NULL && $fieldsValue != false && $fieldsValue != "false") {
                    $userData["custom_fields"]["custom_label_".$contactFields[$fieldsKey]][] = true;
                } else {
                    $userData["custom_fields"]["custom_label_".$contactFields[$fieldsKey]][] = false;
                }
            } else {
                $userData["custom_fields"]["custom_label_".$contactFields[$fieldsKey]] = $fieldsValue;
            }
            if ($userData["custom_fields"]["custom_label_".$contactFields[$fieldsKey]] == "") {
                $userData["custom_fields"]["custom_label_".$contactFields[$fieldsKey]] = null;
            }
        }
    }
}

// Обновление/создание контакта в amoCRM
$result["sendData"] = $userData;
if ($userId != NULL) {
    $auth["app_key"] = $app_key;
    $auth["api_key"] = $api_key;
    $usersData["person"] = $userData;
    $updateContact = json_decode(send_request("https://api.pipelinecrm.com/api/v3/people/".$userId."?".http_build_query($auth), "PUT", $usersData), true);
    if ($updateContact["errors"][$userId] == "Contact not found") {
        $createContact = json_decode(send_request("https://api.pipelinecrm.com/api/v3/people?".http_build_query($auth), "POST", $usersData), true);
        if ($createContact["id"] != NULL) {
            $users[$input["userId"]] = $createContact["id"];
            file_put_contents("users.json", json_encode($users));
        }
        $result = $createContact;
    } else {
        $result['update'] = $updateContact;
    }
} else {
    $auth["app_key"] = $app_key;
    $auth["api_key"] = $api_key;
    $usersData["person"] = $userData;
    $result["auth"] = $auth;
    $result["url"] = "https://api.pipelinecrm.com/api/v3/people?".http_build_query($auth);
    $createContact = json_decode(send_request("https://api.pipelinecrm.com/api/v3/people?".http_build_query($auth), "POST", $usersData), true);
    if ($createContact["id"] != NULL) {
        $users[$input["userId"]] = $createContact["id"];
        file_put_contents("users.json", json_encode($users));
    }
    $result["createContact"] = $createContact;
}

echo json_encode($result);




