<?php

// Данные интеграции с pipelineCRM
$app_key = "";
$api_key = "";
$ss_token = "";

// Сервысные данные
$dir = dirname($_SERVER["PHP_SELF"]);
$url = ((!empty($_SERVER["HTTPS"])) ? "https" : "http") . "://" . $_SERVER["HTTP_HOST"] . $dir;
$url = explode("?", $url);
$url = $url[0];