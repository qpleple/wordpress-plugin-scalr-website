<?php
/*
Plugin Name: Scalr Website
Plugin URI: http://scalr.net
Description: This is where the logic of the Scalr public website lives.
Version: 1.1
Author: Quentin Pleplé
License: Apache
*/

require_once dirname(__FILE__) . "/scalr_utils.php";
require_once dirname(__FILE__) . "/scalr_exceptions.php";

define("TEMPLATES_PATH", dirname(__FILE__) . "/templates/");

function scalr_login_page() {
    if (!isset($_POST['email']) || !isset($_POST['password'])) {
        return;
    }
    
    try {
        // DEBUG
        //login_user($_POST['email'], $_POST['password']);
        return scalr_login_user("quentin@melix.net", "blablabla");
    } catch (Exception $e) {
        if ($e instanceof UserScalrException) {
            return scalr_render_template('error.html', array(
                'error_message' => $e->getMessage(),
            ));
        } else {
            scalr_email_on_exception($e);

            return scalr_render_template('error.html', array(
                'error_message' => "Oops, something went wrong. An email has been sent to us. Contact support if you cannot login anymore.",
            ));
        }
    }
}
add_shortcode('scalr_login_page', 'scalr_login_page');

function scalr_login_user($email, $password) {
    if (empty($email)) {
        throw new UserScalrException(EMAIL_EMPTY_MSG, EMAIL_EMPTY);
    }
    
    if (empty($password)) {
        throw new UserScalrException(PASSWORD_EMPTY_MSG, PASSWORD_EMPTY);
    }    
    
    // Test email valid
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new UserScalrException(BAD_EMAIL_FORMAT_MSG, BAD_EMAIL_FORMAT);
    }
    
    $http_response = scalr_http_post("https://my.scalr.net/guest/xLogin/", array(
        'scalrLogin' => $email,
        'scalrPass' => $password,
    ));
    
    if (!is_array($http_response) || !array_key_exists("code", $http_response) || $http_response['code'] != "200") {
        throw new AdminScalrException(sprintf(BAD_HTTP_RESPONSE_MSG, var_dump_str($http_response)), BAD_HTTP_RESPONSE);
    }
    
    $response = @json_decode($http_response['body']);
    if (empty($response) || !property_exists($response, "success")) {
        throw new AdminScalrException(sprintf(BAD_JSON_RESPONSE_MSG, var_dump_str($http_response['body'])), BAD_JSON_RESPONSE);
    }
    
    if (!$response->success) {
        throw new UserScalrException($response->errorMessage, MY_SCALR_NET_ERROR);
    }
    
    // success
    // TODO : forward cookie
    // TODO : redirect to http://my.scalr.net
    return "SUCCESS";
}

