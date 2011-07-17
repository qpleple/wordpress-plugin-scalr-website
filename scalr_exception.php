<?php

define("")

class ScalrException extends Exception { }

function check($test, $exceptionMessage, $exceptionCode) {
    if (!$test) {
        throw new ScalrException($exceptionMessage, $exceptionCode);
    }
}