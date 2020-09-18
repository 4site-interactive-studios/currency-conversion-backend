<?php

// Allow this resource to be accessible with APIs such as XMLHttpRequest
// from any domain

header('Access-Control-Allow-Origin: *');


$endpoint = 'latest';
$access_key = 'bd86b13c4f91a685545925857ea5eeb6';

//1 day cache life span
$cacheTimer = 86400;

//File name for the cached conversion rates
$cacheFileName = $_SERVER['DOCUMENT_ROOT'].'/shared/fixer-io.txt';
$cacheErrorFile = $_SERVER['DOCUMENT_ROOT'].'shared/errorLog.txt';


function sendEmail($errorMsg)
{
    //Sends email with an error

    $to = 'gemini@peta.org';
    $subject = 'APP-01 - Currency Conversion API Issue';
    $message = $errorMsg;

    mail($to, $subject, $message);
}

function createIssues($errorMsg)
{
    //Requires Github personal access token with the repo scopes all checked

    $issueTitle = 'PETA conversion ticket';
    $github_personal_access_token = '';

    $headers = array("Authorization: token $github_personal_access_token", 'User-Agent: Email-To-Issue-Bot');

    $json = array();
    $json['title'] = $issueTitle;
    $json['body'] = $errorMsg;

    // Create the new GitHub issue
    $ch = curl_init("https://api.github.com/repos/4site-interactive-studios/currency-conversion-backend/issues");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
    curl_exec($ch);
    curl_close($ch);
}

//Check if the file name still exists
function errorCache($cacheErrorFile, $cacheTimer, $errorMsg){

    //Create the error log if the it does not exist and sends the issue and email
    if (!file_exists($cacheErrorFile) or (time() - filemtime($cacheErrorFile) > $cacheTimer)){
        $fileopen = fopen($cacheErrorFile, 'w');
        fwrite($fileopen, $errorMsg);
        fclose($fileopen);
        $cachedFile = file_get_contents($cacheErrorFile);

        createIssues($errorMsg);
        sendEmail($errorMsg);

    }
}

//If the file does not exists or it has been expired, we create a new one
if (!file_exists($cacheFileName) or (time() - filemtime($cacheFileName) > $cacheTimer)) {
    // Initialize CURL:
    $ch = curl_init('http://data.fixer.io/api/' . $endpoint . '?access_key=' . $access_key . '&symbols=USD,MXN,EUR,ARS,COP,CAD,CLP');

    // Gets the data. Increases the amount that the url has been called.
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $json = curl_exec($ch);
    curl_close($ch);

    //Decode JSON response:
    $exchangeRates = json_decode($json, true);

    //If the curl cannot get the API, we output for the console log
    if(!$exchangeRates){
        $errorMsg = 'https://fixer.io/ API is unavailable. Please check the API.';
        errorCache($cacheErrorFile,$cacheTimer,$errorMsg);
        echo $errorMsg;
    }
    //If there is no request error in the curl or the status of the fixer.io is a OK
    else if($exchangeRates['success'] == true){
        $fileopen = fopen($cacheFileName, 'w');
        fwrite($fileopen, $json);
        fclose($fileopen);
        include($cacheFileName);
        unlink($cacheErrorFile);
    }
    //Console log the false state of the Fixer.io
    else if($exchangeRates['success'] == false){
        errorCache($cacheErrorFile,$cacheTimer,$json);
        echo $json;
    }
}
//If it still exists or it's not expired, we get the file
else {
    include($cacheFileName);
}