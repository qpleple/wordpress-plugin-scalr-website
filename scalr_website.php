<?php
/*
Plugin Name: Scalr Website
Plugin URI: http://scalr.net
Description: This is where the logic of the Scalr public website lives.
Version: 1.1
Author: Quentin Pleplé
License: Apache
*/

require_once dirname(__FILE__) . "/scalr_http_utils.php";
require_once dirname(__FILE__) . "/scalr_exception.php";

function scalr_login_page() {
    if (!isset($_POST['email']) || !isset($_POST['password'])) {
        return;
    }
    
    $str = "";
    try {
        login_user($_POST['email'], $_POST['password']);
    } catch (Exception $e) {
        $str .= '<div id="login-error">';
        if ($e instanceof UserScalrException) {
            $str .= $e->getMessage();
        } else {
            email_on_exception($e);
            $str .= "Oops, something went wrong. An email has been sent to us. Contact support if you cannot login anymore.";
            // TODO : var_dump only for debuging, to be deleted
            var_dump($e);
        }
        $str .= '</div>';
    }
    
    return $str;
}
add_shortcode('scalr_login_page', 'scalr_login_page');

function login_user($email, $pass) {
    if (empty($email)) {
        throw new UserScalrException(EMAIL_EMPTY_MSG, EMAIL_EMPTY);
    }
    
    // Test email valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new UserScalrException(BAD_EMAIL_FORMAT_MSG, BAD_EMAIL_FORMAT);
    }
        
    $http_response = http_post("https://my.scalr.net/guest/xLogin/", array(
        'scalrLogin' => $email,
        'scalrPass' => $password,
    ));
    
    if (!is_array($http_response) || !array_key_exists("code", $http_response) || $http_response['code'] != "200") {
        return;
    }
    
    $response = @json_decode($http_response['body']);
    if (empty($response) || !property_exists($response, "success")) {
        return;
    }
    
    if (!$response->success) {
        echo $response->errorMessage;
        return;
    }
}

function email_on_exception($exception) {
    # code...
}