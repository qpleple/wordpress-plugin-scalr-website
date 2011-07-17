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

// Makes the function scalr_send_headers() called on all the pages
// Right after headers have been sent (it is still time to send some)
add_action('send_headers', 'scalr_send_headers', 10, 1);

// On success, send headers:
// - Set-Cookie: to set to client the login cookie
// - Location: to redirect to https://my.scalr.net
// On failure, put the error message into global variable $scalr_login_error_message
function scalr_send_headers($WP) {
    global $scalr_login_error_message;
    
    // Excute only on login page
    if (!preg_match("|^/login(.html)?/?$|", $_SERVER['REQUEST_URI'])) {
        return;
    }
    
    // Execute only if the login form has been submitted
    if (!isset($_POST['email']) || !isset($_POST['password'])) {
        return;
    }
    
    try {
        return scalr_check_and_login_user($_POST['email'], $_POST['password']);
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

// Makes the function scalr_login_page() called on occurence of "[scalr_login_page]"
// Should be in the login form, where to display errors
add_shortcode('scalr_login_page', 'scalr_login_page');

// Displays the content of $scalr_login_error_message that
// is set on failure of login by scalr_send_headers()
function scalr_login_page() {
    global $scalr_login_error_message;
    if (!empty($scalr_login_error_message)) {
        return $scalr_login_error_message;
    }
}

function scalr_check_and_login_user($email, $password) {
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
    
    header('Location: https://my.scalr.net/');
    header('Set-Cookie: ' . $http_response["header"]["Set-Cookie"]);
    die();
}

