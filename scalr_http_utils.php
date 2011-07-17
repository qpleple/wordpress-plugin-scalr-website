<?php
function http_post($url, $args) {
    if (!cURLcheckBasicFunctions()) {
        return;
    }
    
    $ch = curl_init($url);
    
    $encoded = '';
    foreach($args as $name => $value) {
      $encoded .= urlencode($name) . '=' . urlencode($value) . '&';
    }
    // chop off last ampersand
    $encoded = substr($encoded, 0, strlen($encoded) - 1);

    // do a regular HTTP POST
    // content type : application/x-www-form-urlencoded
    curl_setopt($ch, CURLOPT_POST, 1);

    // arguments form POST request
    curl_setopt($ch, CURLOPT_POSTFIELDS,  $encoded);
    
    // curl_exec() will be returning a string instead of outputting out directly
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    // don't include the header in the output
    curl_setopt($ch, CURLOPT_HEADER, 1);
    
    // stop cURL from verifying the peer's certificate
    // we trust my.scalr.net
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $output = curl_exec($ch);

    // closes a cURL session and frees all resources
    curl_close($ch);
    
    return parse_http_response($output);
}

function cURLcheckBasicFunctions() {
    return function_exists("curl_init") && 
           function_exists("curl_setopt") && 
           function_exists("curl_exec") && 
           function_exists("curl_close"); 
}

function parse_http_response($response) { 
    // Split response into header and body sections 
    list($response_headers, $response_body) = explode("\r\n\r\n", $response, 2); 
    $response_header_lines = explode("\r\n", $response_headers); 

    // First line of headers is the HTTP response code 
    $http_response_line = array_shift($response_header_lines); 
    if(preg_match('@^HTTP/[0-9]\.[0-9] ([0-9]{3})@',$http_response_line, $matches)) { $response_code = $matches[1]; } 

    // put the rest of the headers in an array 
    $response_header_array = array(); 
    foreach($response_header_lines as $header_line) { 
        list($header,$value) = explode(': ', $header_line, 2); 
        $response_header_array[$header] .= $value."\n"; 
    } 

    return array(
        'code' => $response_code,
        'header' => $response_header_array,
        'body' => $response_body,
    ); 
}