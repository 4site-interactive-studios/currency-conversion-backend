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

//If the file does not exists or it has been expired, we create a new one
if (!file_exists($cacheFileName) or (time() - filemtime($cacheFileName) > $cacheTimer)) {
    // Initialize CURL:
    $ch = curl_init('http://sdata.fixer.io/api/' . $endpoint . '?access_key=' . $access_key . '&symbols=USD,MXN,EUR,ARS,COP,CAD,CLP');

    // Gets the data. Increases the amount that the url has been called.
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $json = curl_exec($ch);
    curl_close($ch);

    //Decode JSON response:
    $exchangeRates = json_decode($json, true);

    //If the curl cannot get the API, use the old file
    if (!$exchangeRates) {
        //If the file exists use the successful cache file
        if (file_exists($cacheFileName)){
            $cachedFile = file_get_contents($cacheFileName);
            include($cacheFileName);
        }
    }
    //If there is no request error in the curl or the status of the fixer.io is a OK
    else if ($exchangeRates['success'] == true) {
        $fileopen = fopen($cacheFileName, 'w');
        fwrite($fileopen, $json);
        fclose($fileopen);
        $cachedFile = file_get_contents($cacheFileName);
        include($cacheFileName);
    }
    //Console log the false state of the Fixer.io
    else if ($exchangeRates['success'] == false) {
        //If the file exists use the successful cache file
        if (file_exists($cacheFileName)){
            $cachedFile = file_get_contents($cacheFileName);
            include($cacheFileName);
        }
    }
}
//If it still exists or it's not expired, we get the file
else {
    include($cacheFileName);
}