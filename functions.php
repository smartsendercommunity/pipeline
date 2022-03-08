<?php

/////////////////////////////////////////////////
////////////  F U N K T I O N S  /////////////////
/////////////////////////////////////////////////

{
function send_forward($inputJSON, $link){
	
$request = 'POST';	
		
$descriptor = curl_init($link);

 curl_setopt($descriptor, CURLOPT_POSTFIELDS, $inputJSON);
 curl_setopt($descriptor, CURLOPT_RETURNTRANSFER, 1);
 curl_setopt($descriptor, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
 curl_setopt($descriptor, CURLOPT_CUSTOMREQUEST, $request);

    $itog = curl_exec($descriptor);
    curl_close($descriptor);

   		 return $itog;
		
}
function send_bearer($url, $token, $type = "GET", $param = []){
	
		
$descriptor = curl_init($url);

 curl_setopt($descriptor, CURLOPT_POSTFIELDS, json_encode($param));
 curl_setopt($descriptor, CURLOPT_RETURNTRANSFER, 1);
 curl_setopt($descriptor, CURLOPT_HTTPHEADER, array('User-Agent: M-Soft Integration', 'Content-Type: application/json', 'Authorization: Bearer '.$token)); 
 curl_setopt($descriptor, CURLOPT_CUSTOMREQUEST, $type);

    $itog = curl_exec($descriptor);
    curl_close($descriptor);

   		 return $itog;
		
}
function send_request($url, $type = 'GET', $param = [], $encode = "json") {
    $descriptor = curl_init($url);
    
    if ($type != "GET") {
        if ($encode == "urlencode") {
            curl_setopt($descriptor, CURLOPT_POSTFIELDS, http_build_query($param));
            $header[] = 'Content-Type: application/x-www-form-urlencoded';
        } else if ($encode == "json") {
            curl_setopt($descriptor, CURLOPT_POSTFIELDS, json_encode($param));
            $header[] = 'Content-Type: application/json';
        }
    }
    $header[] = 'User-Agent: Mufik Soft(https://mufiksoft.com)';
    
    curl_setopt($descriptor, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($descriptor, CURLOPT_HTTPHEADER, $header); 
    curl_setopt($descriptor, CURLOPT_CUSTOMREQUEST, $type);
    
    $itog = curl_exec($descriptor);
    curl_close($descriptor);
    return $itog;
    
}
}

include('config.php');
$input = json_decode(file_get_contents('php://input'), true);

/////////////////////////////////////////////////
/////////////////////////////////////////////////