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
 * @license		MIT
 * @link		https://github.com/airyland/nodeprint
 * @version	0.0.5
 */
class Oauth extends CI_Controller {

    private $accesstoken;
    private $openid;
    private $appkey;
    private $secretkey;
    
    /**
     * @brief 豆瓣Oauth类词头
     */
    const PREFIX = 'Douban';

    /**
     * @brief authorizeCode请求链接
     */
    protected $authorizeUri;
    
    /**
     * @brief accessToken请求链接
     */
    protected $accessUri;
    
    /**
     * @brief api请求链接
     */
    protected $apiUri = 'https://api.douban.com';
                
    /**
     * @brief 豆瓣应用public key
     */
    protected $clientId;
    
    /**
     * @brief 豆瓣应用secret key
     */
    protected $secret;

    /**
     * @brief callback链接
     */
    protected $redirectUri;

    /**
     * @brief Api权限
     */
    protected $scope;
    
    /**
     * @brief 返回类型，默认使用code
     */
    protected $responseType;
    
    /**
     * @brief 用户授权码
     */
    protected $authorizeCode;

    /**
     * @brief 储存返回的令牌（accessToken,refreshToken）
     */
    protected $tokens;

    /**
     * @brief 通过authorizeCode获得的访问令牌
     */
    protected $accessToken;

    /**
     * @brief 用于刷新accessToken
     */
    protected $refreshToken;

    /**
     * @var 默认请求头信息 
     */
    protected $defaultHeader = array(
                'Content_type: application/x-www-form-urlencoded'
                );

    /**
     * @var 需授权的请求头
     */
    protected $authorizeHeader;
    
    /**
     * @var curl默认设置  
     */
    protected $CURL_OPTS = array(
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT        => 60,
                CURLOPT_USERAGENT      => 'simple-douban-oauth2-0.4',
                );

    function __construct() {
        parent::__construct();
        session_start();
        $this->load->model('user');
        $this->config->load('oauth');
    }

    function index(){
       $params=array(
           'type'=>'douban',
           'clientId'=>$this->config->item('np.oauth.douban.apikey'),
           'secret'=>$this->config->item('np.oauth.douban.secret'),
           'redirectUri'=>'http://nodeprint.com/oauth/douban/callback',
           'scope' =>'douban_basic_common',
           'responseType' => 'code'
       );
        $this->load->library('oauths',$params);

        /* ------------请求用户授权--------------- */

// 如果没有authorizeCode，跳转到用户授权页面
        if ( ! isset($_GET['code'])) {
            $this->oauths->requestAuthorizeCode();
            exit;
        }
// 设置authorizeCode
        $this->oauths->setAuthorizeCode($_GET['code']);
// 通过authorizeCode获取accessToken，至此完成用户授权
        $this->oauths->requestAccessToken();


        /* ------------发送图片广播--------------- */

// 通过豆瓣API发送一条带图片的豆瓣广播
// 我看到豆瓣API小组里很多朋友都卡在了发送图片广播上，那是因为没有设置正确的Content-Type。
// 在PHP中通过curl拓展上传图片必须使用类似“@/home/chou/images/123.png;type=image/png”的模式
// 并且必须在图片绝对路径后指定正确的图片类型，如果没有指定类型会返回“不支持的图片类型错误”。
// 那是因为没有指定图片类型时，上传的文件类型默认为“application/octet-stream”。
        $data = array(
            'source' => $clientId,
            'text' =>'继续修改，继续测试。',
            'image' => '@/home/chou/downloads/123.jpg;type=image/jpeg'
        );

        $miniblog = $this->oauths->api('Miniblog.statuses.POST');
// 如果API需授权，请把makeRequest函数的第三个参数设置true
        $result =$this->oauths->makeRequest($miniblog, $data, true);
    }
    
    function douban(){
        $this->authorizeUri = 'https://www.douban.com/service/auth2/auth';
        $this->accessUri = 'https://www.douban.com/service/auth2/token';
         $appkey=$this->config->item('np.oauth.douban.apikey');
         $secret=$this->config->item('np.oauth.douban.secret');
        if($this->uri->segment(3)==='callback'){
            $params=array(
                'type'=>'douban',
                'clientId'=>$this->config->item('np.oauth.douban.apikey'),
                'secret'=>$this->config->item('np.oauth.douban.secret'),
                'redirectUri'=>'http://nodeprint.com/oauth/douban/callback',
                'scope' =>'douban_basic_common',
                'responseType' => 'code'
            );
            $this->load->library('oauths',$params);

            /* ------------请求用户授权--------------- */

// 如果没有authorizeCode，跳转到用户授权页面
            if ( ! isset($_GET['code'])) {
                $this->oauths->requestAuthorizeCode();
                exit;
            }
// 设置authorizeCode
            $this->oauths->setAuthorizeCode($_GET['code']);
// 通过authorizeCode获取accessToken，至此完成用户授权
            $this->oauths->requestAccessToken();
            print_r($this->oauths);
           echo $this->oauths->makeRequest();


            /* ------------发送图片广播--------------- */

// 通过豆瓣API发送一条带图片的豆瓣广播
// 我看到豆瓣API小组里很多朋友都卡在了发送图片广播上，那是因为没有设置正确的Content-Type。
// 在PHP中通过curl拓展上传图片必须使用类似“@/home/chou/images/123.png;type=image/png”的模式
// 并且必须在图片绝对路径后指定正确的图片类型，如果没有指定类型会返回“不支持的图片类型错误”。
// 那是因为没有指定图片类型时，上传的文件类型默认为“application/octet-stream”。
           /* $data = array(
                'source' => $params['clientId'],
                'text' =>'继续修改，继续测试。',
                'image' => '@/home/chou/downloads/123.jpg;type=image/jpeg'
            );*/

           // $miniblog = $this->oauths->api('Miniblog.statuses.POST');
// 如果API需授权，请把makeRequest函数的第三个参数设置true
           // $result =$this->oauths->makeRequest($miniblog, $data, true);
          //  $_SESSION['code']=$this->input->get('code');
            //print_r($_SESSION);
            
  //$url='https://www.douban.com/service/auth2/token?client_id='.$appkey.'&client_secret='.$secret.'&redirect_uri=http://nodeprint.com/oauth/douban/callback&grant_type=authorization_code&
  //code='.$this->input->get('code');
       
  //$result = $this->curl($url, 'POST', '', '');
  //echo $result;
       // $accessUrl = $this->accessUri;
       // $header = $this->defaultHeader;
       /* $data = array(
                    'client_id' => $appkey,
                    'client_secret' => $secret,
                    'redirect_uri' => 'http://nodeprint.com/oauth/douban/callback',
                    'grant_type' => 'authorization_code',
                    'code' => $this->input->get('code')
                    );*/

       /* $result = $this->curl($accessUrl, 'POST', $header, $data);
        
        echo $result*/;
  
  }else{
             redirect('https://www.douban.com/service/auth2/auth?client_id='.$appkey.'&redirect_uri=http://nodeprint.com/oauth/douban/callback&response_type=code&
  scope=shuo_basic_r,shuo_basic_w,douban_basic_common');
        }
        $this->load->library('s');
        $this->s->assign('/oauth/oauth_qq.html');
      
    }
    
    function join(){
        $this->load->library('s');
        $this->s->assign(array(
            'title'=>'oauth登录'
        ));
        $this->s->display('oauth/oauth_qq.html');
    }
    
    /**
     * @brief 使用CURL模拟请求，并返回取得的数据
     *
     * @param string $url
     * @param string $type
     * @param array $header
     * @param array $data
     *
     * @return object
     */
    protected function curl($url, $type, $header, $data = array())
    {
        $opts = $this->CURL_OPTS;
        $opts[CURLOPT_URL] = $url;
        $opts[CURLOPT_CUSTOMREQUEST] = $type;
        $header[] = 'Expect:'; 
        $opts[CURLOPT_HTTPHEADER] = $header;
        if ($type == 'POST' || $type =='PUT') {
            $opts[CURLOPT_POSTFIELDS] = $data;
        }

        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            die('CURL error: '.curl_error($ch));
        }

        curl_close($ch);  
        return $result;
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
    
     /**
     * @brief 请求豆瓣API,返回包含相关数据的对象
     *
     * @param object $API
     * @param array $data
     * @param boolean 为true时会在header中发送accessToken
     *
     * @return object
     */
    public function makeRequest($api, $data = array(), $authorization = false)
    {
        // API的完整URL
        $url = $this->apiUri.$api->uri;
        $header = $authorization ? $this->getAuthorizeHeader() : $this->defaultHeader;
        $type = $api->type;

        return $this->curl($url, $type, $header, $data);
    }
    
     /**
     * @brief 生成豆瓣用户授权页面完整地址
     *
     * @return string
     */
    protected function getAuthorizeUrl()
    {
        $params = array(
                    'client_id' => $this->clientId,
                    'redirect_uri' => $this->redirectUri,
                    'response_type' => $this->responseType,
                    'scope' => $this->scope
                    );

        return $this->authorizeUri.'?'.http_build_query($params);
    }

    /**
     * @brief 获取Authorization header
     *
     * @return array
     */
    protected function getAuthorizeHeader()
    {
        return $this->authorizeHeader = array('Authorization: Bearer '.$this->accessToken);
    }
    
    /**
     * @brief 注册豆瓣Api
     *
     * @param string $api
     * @param array $params
     *
     * @return object
     */
    public function api($api, $params = array())
    {
        $info = explode('.', $api);
        $class = $info[0];
        $func = $info[1];
        $type = strtoupper($info[2]);

        $doubanApi = self::PREFIX.ucfirst(strtolower($class));
        // 豆瓣Api路径
        $apiFile = dirname(__FILE__).'/api/'.$doubanApi.'.php';
        // 豆瓣Api基类路径
        $basePath = dirname(__FILE__).'/api/DoubanBase.php';
        
        try {
            $this->fileLoader($basePath);
            $this->fileLoader($apiFile);
        } catch(Exception $e) {
            echo 'Apiloader error:'.$e->getMessage();
        }

        $instance = new $doubanApi($this->clientId);
        return $instance->$func($type, $params);
    }
    
    
    function do_join(){
        $this->load->model('user');
        $user=$this->input->post('user_name');
        $pwd=$this->input->post('user_pwd');
        $auth_user=$this->user->login_user($user,$pwd);
        $user_id=$auth_user[2];
        if($auth_user[0]===0){
            $data=array(
                'o_type'=>'douban',
                'user_id'=>$user_id,
                'o_access_token'=>'',
                'o_openid'=>'',
                'o_refresh_token'=>'',
                'o_time'=>  current_time(),
                'o_expire'=>''
            );
            $this->db->insert('oauth',$data);
        }
        //print_r($auth_user);
    }

}

/**
 * @file DoubanBase.php
 * @brief 豆瓣api的Base类
 * @author JonChou <ilorn.mc@gmail.com>
 * @date 2012-11-27
 */

class DoubanBase {

    /**
     * @brief 豆瓣API uri
     */
    protected $uri;

    /**
     * @brief API请求方式
     */
    protected $type;

    /**
     * @brief 豆瓣应用public key
     */
    protected $clientId;

    /**
     * @brief 使用魔术方法获取类属性
     *
     * @param mixed $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
    }
}



/**
 * @file DoubanMiniblog.php
 * @brief 豆瓣广播API
 * @author JonChou <ilorn.mc@gmail.com>
 * @date 2012-12-13
 */

class DoubanMiniblog extends DoubanBase {

    /**
     * @brief 构造函数，初始设置clientId
     *
     * @param string $clientId
     *
     * @return void
     */
    public function __construct($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * @brief 用户对豆瓣广播相关操作
     *
     * @param string $requestType GET,POST,DELETE
     * @param array $params
     *
     * @return object
     */
    public function statuses($requestType, $params)
    {
        $this->type = $requestType;
        switch ($this->type) {
            case 'GET':
            case 'DELETE':
                $this->uri = '/shuo/v2/statuses/'.$params['id'];
                break;
            case 'POST':
                $this->uri = '/shuo/v2/statuses/';
                break;
        }
        return $this;
    }

    /**
     * @brief 获取广播的回复列表
     *
     * @param string $requestType GET
     * @param array $params
     *
     * @return object
     */
    public function commentsList($requestType, $params)
    {
        $this->type = $requestType;
        $this->uri = '/shuo/v2/statuses/'.$params['id'].'/comments';
        unset($params['id']);
        if (!empty($params))
            $this->uri .= '?'.http_build_query($params);
        return $this;
    }

    /**
     * @brief 对广播回复的操作
     *
     * @param string $requestType GET,POST,DELETE
     * @param array $params
     *
     * @return object
     */
    public function comment($requestType, $params)
    {
        $this->type = $requestType;
        switch ($this->type) {
            case 'GET':
            case 'DELETE':
                $this->uri = '/shuo/v2/statuses/comment/'.$params['id'];
                break;
            case 'POST':
                $this->uri = '/shuo/v2/statuses/'.$params['id'].'/comments';
                break;
        }
        return $this;
    }

    /**
     * @brief 对转播相关操作
     *
     * @param string $requestType GET,POST
     * @param array $params
     *
     * @return object
     */
    public function reshare($requestType, $params)
    {
        $this->type = $requestType;
        $this->uri = '/shuo/v2/statuses/'.$params['id'].'/reshare';
        return $this;
    }

    /**
     * @brief 赞某条广播
     *
     * @param string $requestType GET,POST
     * @param array $params
     *
     * @return object
     */
    public function like($requestType, $params)
    {
        $this->type = $requestType;
        $this->uri = '/shuo/v2/statuses/'.$params['id'].'/like';
        return $this;
    }

    /**
     * @brief 获取用户关注列表。
     *
     * @param string $requestType GET
     * @param array $params
     *
     * @return object
     */
    public function following($requestType, $params)
    {
        $this->type = $requestType;
        $this->uri = '/shuo/v2/users/'.$params['id'].'/following';
        return $this;
    }

    /**
     * @brief 获取用户关注者列表。
     *
     * @param string $requestType GET
     * @param array $params
     *
     * @return object
     */
    public function followers($requestType, $params)
    {
        $this->type = $requestType;
        $this->uri = '/shuo/v2/users/'.$params['id'].'/followers';
        return $this;
    }


    /**
     * @brief 获取共同关注列表。
     *
     * @param string $requestType GET
     * @param array $params
     *
     * @return object
     */
    public function followInCommon($requestType, $params)
    {
        $this->type = $requestType;
        $this->uri = '/shuo/v2/users/'.$params['id'].'/follow_in_common';
        return $this;
    }

    /**
     * @brief 获取关注的人关注了该用户的列表。
     *
     * @param $requestType
     * @param $params
     *
     * @return
     */
    public function suggestions($requestType, $params)
    {
        $this->type = $requestType;
        $this->uri = '/shuo/v2/users/'.$params['id'].'/following_followers_of';
        return $this;
    }

    /**
     * @brief 将指定用户加入黑名单
     *
     * @param $requestType
     * @param $params
     *
     * @return
     */
    public function block($requestType, $params)
    {
        $this->type = $requestType;
        $this->uri = '/shuo/v2/users/'.$params['id'].'/block';
        return $this;

    }

    /**
     * @brief 关注一个用户
     *
     * @param string $requestType POST
     *
     * @return object
     */
    public function follow($requestType)
    {
        $this->type = $requestType;
        $this->uri = '/shuo/v2/friendships/create';
        return $this;
    }

    /**
     * @brief 取消关注
     *
     * @param string $requestType POST
     *
     * @return object
     */
    public function unfollow($requestType)
    {
        $this->type = $requestType;
        $this->uri = '/shuo/v2/friendships/destroy';
        return $this;

    }

    /**
     * @brief 获取两个用户的关系
     *
     * @param string $requestType GET
     * @param array $params source,source_id,target_id
     *
     * @return object
     */
    public function show($requestType, $params)
    {
        $this->type = $requestType;
        $this->uri = '/shuo/v2/friendships/show?'.http_build_query($params);
        return $this;
    }


    /**
     * @brief 获取当前登录用户及其所关注用户的最新广播(友邻广播)
     *
     * @param string $requestType GET
     * @param array $params
     *
     * @return object
     */
    public function homeTimeline($requestType, $params)
    {
        $this->type = $requestType;
        $query = !empty($params) ? '?'.http_build_query($params) : null;
        $this->uri = '/shuo/v2/statuses/home_timeline'.$query;
        return $this;
    }

    /**
     * @brief 获取用户发表的广播列表
     *
     * @param string $requestType GET
     * @param array $params
     *
     * @return object
     */
    public function userTimeline($requestType, $params)
    {
        $this->type = $requestType;
        $this->uri = '/shuo/v2/statuses/user_timeline/'.$params['user'];
        unset($params['user']);
        if (!empty($params))
            $this->uri .= '?'.http_build_query($params);
        return $this;
    }

}


/**
 * @file DoubanUser.php
 * @brief 豆瓣用户API
 * @author JonChou <ilorn.mc@gmail.com>
 * @date 2012-12-03
 */

class DoubanUser extends DoubanBase {

    /**
     * @brief 构造函数，初始设置clientId
     *
     * @param string $clientId
     *
     * @return void
     */
    public function __construct($clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * @brief 获取用户个人信息接口
     *
     * @param string $requestType HTTP请求方式
     * @param array $params api需要的参数
     *
     * @return object
     */
    public function info($requestType, $params)
    {
        $this->type = $requestType;
        $this->uri = '/v2/user/'.$params['id'].'?apikey='.$this->clientId;
        return $this;
    }

    /**
     * @brief 获取当前登录用户信息接口
     *
     * @param string $requestType
     * @param array $params
     *
     * @return object
     */
    public function me($requestType, $params)
    {
        $this->type = $requestType;
        $this->uri = '/v2/user/~me';
        return $this;
    }

    /**
     * @brief 搜索用户接口
     *
     * @param string $requestType
     * @param array $params
     *
     * @return object
     */
    public function search($requestType, $params)
    {
        $this->type = $requestType;
        $this->uri = '/v2/user?'.http_build_query($params);
        return $this;
    }
}



/* End of file oauth.php */
/* Location: ./application/controllers.oauth.php */