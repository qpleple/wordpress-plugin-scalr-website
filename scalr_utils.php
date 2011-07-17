<?php
// Make a HTTP POST request to $url with the arguments
// in the array $arg
function scalr_http_post($url, $args) {
    if (!scalr_check_curl_functions()) {
        return;
    }
    
    $ch = curl_init($url);
    
    $encoded = '';
    foreach($args as $name => $value) {
      // TODO : reactivate urlencode
      //$encoded .= urlencode($name) . '=' . urlencode($value) . '&';
      $encoded .= ($name) . '=' . ($value) . '&';
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
    
    return scalr_parse_http_response($output);
}

function scalr_check_curl_functions() {
    return function_exists("curl_init") && 
           function_exists("curl_setopt") && 
           function_exists("curl_exec") && 
           function_exists("curl_close"); 
}

// Parse an HTTP response and return an array with :
// - code
// - headers in an array
// - body
function scalr_parse_http_response($response) { 
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

// A var_dump() that returns a string
function scalr_var_dump_str($obj) {
    ob_start();
    var_dump($obj);
    return ob_get_clean();
}

// returns a string with the content of the file $templateName
// and replacing the value given in the array $args
function scalr_render_template($templateName, $args) {
    $templateFullPath = TEMPLATES_PATH . $templateName;
    if (!file_exists($templateFullPath)) {
        throw new AdminScalrException(sprintf(TEMPLATE_NOT_FOUND_MSG, $templateFullPath), TEMPLATE_NOT_FOUND);
    }
    
    $content = @file_get_contents($templateFullPath);
    foreach ($args as $key => $value) {
        $content = str_replace('{' . $key . '}', $value, $content);
    }
    
    return $content;
}

// Sends an email to the administrator of the blog (set in Settings > General)
// with the content of the exception given
function scalr_email_on_exception($exception) {
    $content = scalr_render_template('email_on_error.eml', array(
        'error_message' => $exception,
    ));
    wp_mail(get_option('admin_email'), "[Scalr Login] Server Error", $content);
}