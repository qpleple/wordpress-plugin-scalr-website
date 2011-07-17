<?php

define("EMAIL_EMPTY", 1001);
define("EMAIL_EMPTY_MSG", "No email address given.");

define("BAD_EMAIL_FORMAT", 1002);
define("BAD_EMAIL_FORMAT_MSG", "Wrong format for email address given.");

define("PASSWORD_EMPTY", 1003);
define("PASSWORD_MSG", "No password given.");


// Message exception will be displayed to the user
class UserScalrException extends Exception { }

// Message exception will not be displayed to the user
// and an email will be sent to Scalr team
class AdminScalrException extends Exception { }