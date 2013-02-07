<?php

/**
 * Common helpers
 * @author airyland <i@mao.li>
 */

/**
 * count offset
 *
 * @param int $page
 * @param int $no
 * @return int
 */
function count_offset($page, $no)
{
    return ($page - 1) * $no;
}

/**
 * get formatted time
 *
 * @return string
 */
function current_time()
{
    return date('Y-m-d H:i:s');
}

/**
 * get random strings
 *
 * @param int $length
 * @param string $valid_chars
 * @return string
 */
function get_random_strings($length, $valid_chars = 'abcdefghjkmnpqrsuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789')
{
    $random_string = "";
    $num_valid_chars = strlen($valid_chars);
    for ($i = 0; $i < $length; $i++) {
        $random_pick = mt_rand(1, $num_valid_chars);
        $random_char = $valid_chars[$random_pick - 1];
        $random_string .= $random_char;
    }
    return $random_string;
}

/**
 * Generate an encoded hash
 *
 * @param string $string
 * @param string $operation 'DECODE'<=>'ENCODE'
 * @param string $key
 * @param int $expiry
 * @return string
 */
function authcode($string, $operation = 'DECODE', $key = 'justtest', $expiry = 0)
{
    $ckey_length = 4; //length can vary from 0 to 32;
    $key = md5($key ? $key : 'justtest');
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }
    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

/**
 * get random string, used as the user pwd salt
 *
 * @param int $length
 * @return string
 */
function get_random_string($length = 5)
{
    $code = md5(uniqid(rand(), true));
    return substr($code, 0, $length);
}

/**
 * generate a friendly date
 * @param date $sTime
 * @param string $type
 * @param string $alt
 * @return string
 */
function friendlyDate($sTime, $type = 'normal', $alt = 'false')
{
    $cTime = time();
    $dTime = $cTime - $sTime;
    $dDay = intval(date("Ymd", $cTime)) - intval(date("Ymd", $sTime));
    $dYear = intval(date("Y", $cTime)) - intval(date("Y", $sTime));
    if ($type == 'normal') {
        if ($dTime < 60) {
            echo $dTime . " seconds ago";
        } elseif ($dTime < 3600) {
            echo intval($dTime / 60) . " minutes ago";
        } elseif ($dTime >= 3600 && $dDay == 0) {
            echo intval($dTime / 3600) . " hours ago";
        } elseif ($dYear == 0) {
            echo date("m-d ,H:i", $sTime);
        } else {
            echo date("Y-m-d ,H:i", $sTime);
        }
    } elseif ($type == 'full') {
        echo date("Y-m-d , H:i:s", $sTime);
    }
}

/**
 * check if user has signed in
 *
 * @return bool
 */
function is_login()
{
    return (!empty($_COOKIE['NP_auth'])) ? TRUE : FALSE;
}

/**
 * get user info from cookie
 * @return array
 */
function get_user()
{
    $e = 2;
    if (empty($_COOKIE['NP_auth'])) {
        $user_id = '';
        $user_name = '';
    } else {
        list($user_id, $user_name) = explode("\t", authcode($_COOKIE['NP_auth'], 'DECODE'));
        if ($user_id && $user_name)
            $e = 1;
    }
    return array('error' => $e, 'user_id' => $user_id, 'user_name' => $user_name);
}

/**
 * check if user has logined, if not, output error
 *
 */
function check_login()
{
    $user = get_user();
    if ($user['error'] == 2) {
        echo json_encode(array('error' => -1, 'msg' => 'not logined yet'));
        exit;
    }
}


/**
 * output json data
 *
 * @param int $error
 * @param string $name
 * @param mixed $data
 * @return void
 */
function json_output($error, $name = '', $data = '')
{
    $out = array();
    $out['error'] = $error;
    if ($name)
        $out[$name] = $data;
    header('content-Type: application/json; charset=UTF-8', true);
    echo json_encode($out);
    exit;
}


/**
 * cut str
 *
 * @access public
 * @param string $str
 * @param integer $start
 * @param integer $length
 * @param string $trim
 * @param string $charset
 * @return string
 */
function cut_str($str, $start, $length, $trim = "...", $charset = 'UTF-8')
{
    if (function_exists('mb_get_info')) {
        $iLength = mb_strlen($str, $charset);
        $str = mb_substr($str, $start, $length, $charset);
        return ($length < $iLength - $start) ? $str . $trim : $str;
    } else {
        preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $str, $info);
        $str = join("", array_slice($info[0], $start, $length));
        return ($length < (sizeof($info[0]) - $start)) ? $str . $trim : $str;
    }
}

function get_avatar($user_id)
{
    $e = 0;
    if (file_exists(APPPATH . '../img/avatar/l/' . $user_id . '.png'))
        $e = $user_id;
    return $e;
}

function valid_email($address)
{
    return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $address)) ? FALSE : TRUE;
}

function alert($msg, $url)
{
    echo '<script type="text/javascript">alert("' . $msg . '");window.location.href="' . $url . '";</script>';
    exit;
}

/**
 * 加载语言包
 * @return array
 */
function load_lang()
{
    include(APPPATH . 'cache/site/config_cache.php');
    include(APPPATH . 'helpers/lang_helper.php');
    return lang($config['lang']);
}

function get_lang()
{
    include(APPPATH . 'cache/site/config_cache.php');
    return $config['lang'];
}

if (!function_exists('mb_strlen')) {
    function mb_strlen($str, $enc = '')
    {
        $counts = count_chars($str);
        $total = 0;
        // Count ASCII bytes
        for ($i = 0; $i < 0x80; $i++) {
            $total += $counts[$i];
        }
        // Count multibyte sequence heads
        for ($i = 0xc0; $i < 0xff; $i++) {
            $total += $counts[$i];
        }
        return $total;
    }

}

/**
 * Shortcut function for ET::translate().
 *
 * @see ET::translate()
 */
function T($string, $default = false)
{
    $_ci = & get_instance();
    $_ci->lang->load('time', get_lang());
    return $_ci->lang->line($string);
}


/**
 * Translate a string to its normal form or its plurular form, depending on an amount.
 *
 * @param string $string The string to translate (singular).
 * @param string $pluralString The string to translate (plurular).
 * @param int $amount The amount.
 */
function Ts($string, $pluralString, $amount)
{
    return sprintf(T($amount == 1 ? $string : $pluralString), $amount);
}


/**
 * Get a human-friendly string (eg. 1 hour ago) for how much time has passed since a given time.
 *
 * @param int $then UNIX timestamp of the time to work out how much time has passed since.
 * @param bool $precise Whether or not to return "x minutes/seconds", or just "a few minutes".
 * @return string A human-friendly time string.
 */
function relative_time($then, $precise = TRUE)
{
    // If there is no $then, we can only assume that whatever it is never happened...
    if (!$then) return T("never");
    // Work out how many seconds it has been since $then.
    $ago = time() - $then;
    // If $then happened less than 1 second ago (or is yet to happen,) say "Just now".
    if ($ago < 1) return T("just now");
    // If this happened over a year ago, return "x years ago".
    if ($ago >= ($period = 60 * 60 * 24 * 365.25)) {
        $years = floor($ago / $period);
        return Ts("%d year ago", "%d years ago", $years);
    } // If this happened over two months ago, return "x months ago".
    elseif ($ago >= ($period = 60 * 60 * 24 * (365.25 / 12)) * 2) {
        $months = floor($ago / $period);
        return Ts("%d month ago", "%d months ago", $months);
    } // If this happend over a week ago, return "x weeks ago".
    elseif ($ago >= ($period = 60 * 60 * 24 * 7)) {
        $weeks = floor($ago / $period);
        return Ts("%d week ago", "%d weeks ago", $weeks);
    } // If this happened over a day ago, return "x days ago".
    elseif ($ago >= ($period = 60 * 60 * 24)) {
        $days = floor($ago / $period);
        return Ts("%d day ago", "%d days ago", $days);
    } // If this happened over an hour ago, return "x hours ago".
    elseif ($ago >= ($period = 60 * 60)) {
        $hours = floor($ago / $period);
        return Ts("%d hour ago", "%d hours ago", $hours);
    }
    // If we're going for a precise value, go on to test at the minute/second level.
    if ($precise) {
        // If this happened over a minute ago, return "x minutes ago".
        if ($ago >= ($period = 60)) {
            $minutes = floor($ago / $period);
            return Ts("%d minute ago", "%d minutes ago", $minutes);
        } // Return "x seconds ago".
        elseif ($ago >= 1) return Ts("%d second ago", "%d seconds ago", $ago);

    }
    // Otherwise, just return "Just now".
    return T("just now");
}


function get_relative_time($time)
{
    echo relative_time($time);
}

/**
 * convert thousands into K
 * @param int $count
 * @return mixed
 */
function number2K($count)
{
    return $count > 999 ? sprintf('%.1f', $count / 1000) . 'K' : $count;
}


function output_1px_img()
{
    $file = 'img/_.gif';
    header('Content-Type:image/gif');
    readfile($file);
}