<?php

define("EMAIL_EMPTY", 1001);
define("EMAIL_EMPTY_MSG", "No email address given.");

define("BAD_EMAIL_FORMAT", 1002);
define("BAD_EMAIL_FORMAT_MSG", "Wrong format for email address given.");

define("PASSWORD_EMPTY", 1003);
define("PASSWORD_EMPTY_MSG", "No password given.");

define("BAD_HTTP_RESPONSE", 1003);
define("BAD_HTTP_RESPONSE_MSG", "Bad server response. Received : %s");

define("BAD_JSON_RESPONSE", 1004);
define("BAD_JSON_RESPONSE_MSG", "Server response is bad Json. Received : %s");

define("TEMPLATE_NOT_FOUND", 1005);
define("TEMPLATE_NOT_FOUND_MSG", "Template \"%s\" not found.");

// Error from my.scalr.net, when the response is like
// { "success" : false, "errorMessage" : "..." }
define("MY_SCALR_NET_ERROR", 1006);

// Message exception will be displayed to the user
class UserScalrException extends Exception { }

// Message exception will not be displayed to the user
// and an email will be sent to Scalr team
class AdminScalrException extends Exception { }