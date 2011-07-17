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

// TODO: error handling, no more blank return
function scalr_login_page() {
    if (!(isset($_POST['email']) && isset($_POST['password']))) {
        return;
    }
    
    //// Test email valid
    //if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    //    return;
    //}
    
    $http_response = http_post("https://my.scalr.net/guest/xLogin/", array(
        'scalrLogin' => $_POST['email'],
        'scalrPass' => $_POST['password'],
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
add_shortcode('scalr_login_page', 'scalr_login_page');