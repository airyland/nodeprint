<?php

!defined('BASEPATH') && exit('No direct script access allowed');
header("Content-type:text/html;charset='utf-8'");

/**
 * NodePrint
 *
 * 基于HTML5及CSS3的轻论坛程序
 * 
 * NodePrint is an open source BBS System built on PHP and MySQL.
 *
 * @package	NodePrint
 * @author		airyland <i@mao.li>
 * @copyright	Copyright (c) 2012, mao.li.
 * @license		GNU General Public License 2.0
 * @link		https://github.com/airyland/nodeprint
 * @version	0.0.5
 */
class Oauth extends CI_Controller {

    private $accesstoken;
    private $openid;
    private $appkey;
    private $secretkey;

    function __construct() {
        parent::__construct();
        session_start();
        $this->load->model('user');
    }

    function get_qq_config() {
        return array(
            'appid' => 100343915,
            'appkey' => "94673e948392e10483275eb02890c613",
            'callback' => base_url() . "oauth/qq_callback",
            'scope' => "get_user_info,add_share,list_album,add_album,upload_pic,add_topic,add_one_blog,add_weibo"
        );
    }

    function qq_oauth() {
        $config = $this->get_qq_config();

        //QQ授权api接口.按需调用
        function qq_login($appid, $scope, $callback) {
            $_SESSION['state'] = md5(uniqid(rand(), TRUE)); //CSRF protection
            $login_url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id="
                    . $appid . "&redirect_uri=" . urlencode($callback)
                    . "&state=" . $_SESSION['state']
                    . "&scope=" . $scope;
            header("Location:$login_url");
        }

        qq_login($config["appid"], $config["scope"], $config["callback"]);
    }

    function qq_callback() {
        //print_r($_SESSION);
        // echo $_REQUEST['state'];
        //  echo $_SESSION['state'];
        /**
          if ($_REQUEST['state'] == $_SESSION['state']) { //csrf
          $token_url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&"
          . "client_id=" . $_SESSION["appid"] . "&redirect_uri=" . urlencode($_SESSION["callback"])
          . "&client_secret=" . $_SESSION["appkey"] . "&code=" . $_REQUEST["code"];

          $response = $this->get_url_contents($token_url);
          print_r($response);
          if (strpos($response, "callback") !== false) {
          $lpos = strpos($response, "(");
          $rpos = strrpos($response, ")");
          $response = substr($response, $lpos + 1, $rpos - $lpos - 1);
          $msg = json_decode($response);
          if (isset($msg->error)) {
          echo "<h3>error:</h3>" . $msg->error;
          echo "<h3>msg  :</h3>" . $msg->error_description;
          exit;
          }
          }

          $params = array();
          parse_str($response, $params);

          //debug
          //print_r($params);
          //set access token to session
          $_SESSION["access_token"] = $params["access_token"];
          } else {
          echo("The state does not match. You may be a victim of CSRF.");
          }
         * * */
        $info = $this->get_user_info();
        $this->load->library('s');
        $this->s->assign(array(
            'user' => $info,
            'title' => '使用QQ账号登录'
        ));
        $this->s->display('oauth_qq.html');
    }

    function get_openid() {
        $graph_url = "https://graph.qq.com/oauth2.0/me?access_token="
                . $_SESSION['access_token'];

        $str = $this->get_url_contents($graph_url);
        print_r($str);
        if (strpos($str, "callback") !== false) {
            $lpos = strpos($str, "(");
            $rpos = strrpos($str, ")");
            $str = substr($str, $lpos + 1, $rpos - $lpos - 1);
        }

        $user = json_decode($str);
        if (isset($user->error)) {
            echo "<h3>error:</h3>" . $user->error;
            echo "<h3>msg  :</h3>" . $user->error_description;
            exit;
        }

        //debug
        //echo("Hello " . $user->openid);
        //set openid to session
        $_SESSION["openid"] = $user->openid;
    }

    function get_url_contents($url) {
        if (ini_get("allow_url_fopen") == "1")
            return file_get_contents($url);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    function get_user_info() {
        $get_user_info = "https://graph.qq.com/user/get_user_info?"
                . "access_token=" . $_SESSION['access_token']
                . "&oauth_consumer_key=" . $_SESSION["appid"]
                . "&openid=" . $_SESSION["openid"]
                . "&format=json";

        $info = $this->get_url_contents($get_user_info);
        return json_decode($info, true);
    }

    /**
     * 获取用户头像
     * @param string $url 头像地址
     * @param int $user_id 用户id
     */
    function fetch_avatar($url, $user_id) {
        
    }

}

/* End of file oauth.php */
/* Location: ./application/controllers.oauth.php */