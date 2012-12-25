<?php 


function is_mail($value) {
    return preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', trim($value));
}

function is_uri($value) {
    return preg_match('/^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&amp;_~`@[\]\':+!]*([^&lt;>\"\"])*$/', trim($value));
}


function is_length($value, $min = 0, $max= 0) {
    $value = trim($value);
    if ($min != 0 &&strlen($value) < $min) return false;
    if ($max != 0 &&strlen($value) > $max) return false;
    return true; 
}


 function is_safe_account($value) {
    return preg_match ("/^[a-zA-Z]{1}[a-zA-Z0-9_\.]{3,31}$/", $value);
}

function is_safe_nickname($value) {
    return preg_match ("/^[-\x{4e00}-\x{9fa5}a-zA-Z0-9_\.]{2,10}$/u", $value);
}

function is_safe_password($str) {
    if (preg_match('/[\x80-\xff]./', $str) || preg_match('/\'|"|\"/', $str) || strlen($str) < 6 || strlen($str) >16 ){
        return false;
    }
    return true;
}

/**
*是否为纯数字
*/







?>