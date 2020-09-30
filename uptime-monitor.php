<?php 
//1 day 30min
$cacheTimer = 88200;
$cacheFileName = 'cache/cachedConverter.txt';

if((time() - filemtime($cacheFileName) > $cacheTimer)){
    //sending error
    http_response_code(500);
}
else{
    echo 'OK';
}