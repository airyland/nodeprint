<?php

/**
 * Common helpers
 * @author airyland <i@mao.li>
 */
function count_offset($page, $no) {
    return ($page - 1) * $no;
}

function current_time() {
    return date('Y-m-d H:i:s');
}

function get_random_strings($length, $valid_chars = 'abcdefghjkmnpqrsuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789') {
    // start with an empty random string
    $random_string = "";
    // count the number of chars in the valid chars string so we know how many choices we have
    $num_valid_chars = strlen($valid_chars);
    // repeat the steps until we've created a string of the right length
    for ($i = 0; $i < $length; $i++) {
        // pick a random number from 1 up to the number of valid chars
        $random_pick = mt_rand(1, $num_valid_chars);
        // take the random character out of the string of valid chars
        // subtract 1 from $random_pick because strings are indexed starting at 0, and we started picking at 1
        $random_char = $valid_chars[$random_pick - 1];
        // add the randomly-chosen char onto the end of our string so far
        $random_string .= $random_char;
    }
    // return our finished random string
    return $random_string;
}

function authcode($string, $operation = 'DECODE', $key = 'justtest', $expiry = 0) {

    $ckey_length = 4; //note 随机密钥长度 取值 0-32;
    //note 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
    //note 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
    //note 当此值为 0 时，则不产生随机密钥

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
 * get random string, used as the salt
 */
function get_radom_string($length = 5) {
    $code = md5(uniqid(rand(), true));
    return substr($code, 0, $length);
}

function is_email($email) {
    return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", $email);
}

function friendlyDate($sTime, $type = 'normal', $alt = 'false') {
//date_default_timezone_set('PRC');
    //sTime=源时间，cTime=当前时间，dTime=时间差
    $cTime = time();
    $dTime = $cTime - $sTime;
    $dDay = intval(date("Ymd", $cTime)) - intval(date("Ymd", $sTime));
    $dYear = intval(date("Y", $cTime)) - intval(date("Y", $sTime));
    //normal：n秒前，n分钟前，n小时前，日期
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
        //full: Y-m-d , H:i:s
    } elseif ($type == 'full') {
        echo date("Y-m-d , H:i:s", $sTime);
    }
}

function auto_inner_link($text) {
    
}

function filter_text($text) {
//markdown转换
//检测markdown选项开启时再转换
//图片转换
    $text = auto_image($text);
//链接转换
    $text = auto_link($text);
//视频转换
//换行转换为</br>
    $text = str_replace(array("\r\n", "\r", "\n"), "</br>", $text);

//过滤多余html标签
    return $text;
}

function is_ajax() {
    $e = FALSE;
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        $e = TRUE;
    return $e;
}

function is_login() {
    return (!empty($_COOKIE['vx_auth'])) ? TRUE : FALSE;
}

function get_user() {
    $e = 2;
    if (empty($_COOKIE['vx_auth'])) {
        $user_id = '';
        $user_name = '';
    } else {
        list($user_id, $user_name) = explode("\t", authcode($_COOKIE['vx_auth'], 'DECODE'));
        if ($user_id && $user_name)
            $e = 1;
    }
    return array('error' => $e, 'user_id' => $user_id, 'user_name' => $user_name);
}

/**
 * check if user has logined, if not, output error
 *
 */
function check_login() {
    $user = get_user();
    if ($user['error'] == 2) {
        echo json_encode(array('error' => -1, 'msg' => 'not logined yet'));
        exit;
    }
}

function check_permission() {
    
}

function is_url($value) {
    return preg_match('/^http:\/\/[A-Za-z0-9]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/', trim($value));
}

/**
 * 输出JSON数据
 * @param int $error 错误类型
 * @param string $name 数据命名
 * @param array $data 数据体
 */
function json_output($error, $name = '', $data = '') {
    $out = array();
    $out['error'] = $error;
    if ($name)
        $out[$name] = $data;
    header('content-Type: application/json; charset=UTF-8', true);
    echo json_encode($out);
    exit;
}

//function redirect($url,$message=''){
// header("location:".base_url().url);
//}

/**
 * 宽字符串截字函数
 *
 * @access public
 * @param string $str 需要截取的字符串
 * @param integer $start 开始截取的位置
 * @param integer $length 需要截取的长度
 * @param string $trim 截取后的截断标示符
 * @param string $charset 字符串编码
 * @return string
 */
function cut_str($str, $start, $length, $trim = "...", $charset = 'UTF-8') {
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

function get_avatar($user_id) {
    $e = 0;
    if (file_exists(APPPATH . '../img/avatar/l/' . $user_id . '.png'))
        $e = $user_id;
    return $e;
}

function valid_email($address) {
    return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $address)) ? FALSE : TRUE;
}

function alert($msg, $url) {
    echo '<script type="text/javascript">alert("' . $msg . '");window.location.href="' . $url . '";</script>';
    exit;
}

/**
 * 加载语言包
 * @return array
 */
function load_lang() {
    include(APPPATH . 'cache/site/config_cache.php');
    include(APPPATH . 'helpers/lang_helper.php');
    return lang($config['lang']);
}

function get_lang(){
    include(APPPATH . 'cache/site/config_cache.php');
    return $config['lang'];
}

if(!function_exists('mb_strlen')){
    function mb_strlen( $str, $enc = '' ) {
        $counts = count_chars( $str );
        $total = 0;
        // Count ASCII bytes
        for( $i = 0; $i < 0x80; $i++ ) {
            $total += $counts[$i];
        }
        // Count multibyte sequence heads
        for( $i = 0xc0; $i < 0xff; $i++ ) {
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
    $_ci = &get_instance();
    $_ci->lang->load('time',get_lang());
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
function relative_time($then, $precise = false)
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
    }

    // If this happened over two months ago, return "x months ago".
    elseif ($ago >= ($period = 60 * 60 * 24 * (365.25 / 12)) * 2) {
        $months = floor($ago / $period);
        return Ts("%d month ago", "%d months ago", $months);
    }

    // If this happend over a week ago, return "x weeks ago".
    elseif ($ago >= ($period = 60 * 60 * 24 * 7)) {
        $weeks = floor($ago / $period);
        return Ts("%d week ago", "%d weeks ago", $weeks);
    }

    // If this happened over a day ago, return "x days ago".
    elseif ($ago >= ($period = 60 * 60 * 24)) {
        $days = floor($ago / $period);
        return Ts("%d day ago", "%d days ago", $days);
    }

    // If this happened over an hour ago, return "x hours ago".
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
        }

        // Return "x seconds ago".
        elseif ($ago >= 1) return Ts("%d second ago", "%d seconds ago", $ago);

    }

    // Otherwise, just return "Just now".
    return T("just now");
}


function get_relative_time($time){
    echo relative_time($time);
}

/**
 * convert thousands into K
 * @param int $count
 * @return mixed
 */
function number2K($count){
    return $count>999?sprintf('%.1f', $count/1000).'K':$count;
}

/**
 * Returns whether or not the user is using a mobile device.
 *
 * @return bool
 */
function is_mobile()
{
    static $is_mobile = null;
    if (is_null($is_mobile)) {
        // from http://detectmobilebrowser.com/ by Chad Smith. Thanks Chad!
        $userAgent = $_SERVER["HTTP_USER_AGENT"];
        $is_mobile = (preg_match("/android|avantgo|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)
|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)
|vodafone|wap|windows (ce|phone)|xda|xiino/i", $userAgent) || preg_match("/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a
wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi
(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-
s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\
-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)
|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le
(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi
(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne
((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-
2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)
|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-
|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)
|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)
|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i", substr($userAgent, 0, 4)));
    }
    return $is_mobile;
}
