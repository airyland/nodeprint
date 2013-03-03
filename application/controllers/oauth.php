<?php

!defined('BASEPATH') && exit('No direct script access allowed');
/**
 * NodePrint
 *
 * Simple and Elegant Forum Software
 *
 * @package         NodePrint
 * @author          airyland <i@mao.li>
 * @copyright       Copyright (c) 2013, mao.li
 * @license         MIT
 * @link            https://github.com/airyland/nodeprint
 * @version         0.0.5
 */

/**
 * 1.get service provider
 * 2.load config
 * 3.code ?
 * 4.1 header to request code
 * 4.2 request access token
 * 5. request user info
 * 6.save into oauth table
 * 7.insert into user table
 * 8.fetch avatar
 *
 *
 */

class Oauth extends CI_Controller
{

    protected $service;
    protected $allow_service = array('douban', 'qq', 'weibo','github','create_account');
    private $token;
    private $openid;
    private $user_info;
    public $service_map =array(
        'qq'=>'QQ',
        'douban'=>'豆瓣',
        'weibo'=>'微博',
        'github'=>'Github'
        );

    function __construct()
    {
        parent::__construct();
        $segment =$this->uri->segment(2);
        $this->config->load('oauth');
        if(!$segment) show_404();
        if(!in_array($segment,$this->allow_service)){
            show_404();
            exit;
        } 
        //get service name
        $this->service=$segment;
        session_start();
       // session_destroy();


        if($segment!=='create_account'){
                include APPPATH.'libraries/NPauth/strategy/'.$this->service.'/'.$this->service.'.php';
                $service = ucfirst($segment);
                $this->oauth=new $service($this->get_apikey(),$this->get_secret());
                $this->load->model('user');

                // check if code is provided, if not, redirect to get authorized
                if(!$this->input->get('code')){
                    $this->oauth->requestAuthorizeCode();
                }
                // callback handler
                if($this->uri->segment(3)==='callback'){
                    $this->oauth->setAuthorizeCode($this->input->get('code'));
                    $this->oauth->requestAccessToken();
                    // get openid
                    $openid =$this->oauth->openid;
                    // get token info
                    $token_info = $this->oauth->token_info;
                    // get user info
                    $this->oauth->parse_user_data();

                   // print_r($this->oauth);
                    // check oauth history
                    print_r($this->oauth->user_info);
                    echo '<br/><br/><br/><br/>';
                    print_r($_SESSION);
                    echo '<br/><br/><br/><br/>';
                    print_r($this->oauth->token_info);
                    echo '<br/><br/><br/><br/>';
                    print_r($_SESSION['user_data']);
                    echo '<br/><br/><br/><br/>';
                    echo '<br/><br/><br/><br/>';
                    $has_oauthed = $this->check_oauth($this->service,$openid);

                    if($has_oauthed>0){
                        $this->load->model('user');
                        $this->user->signin_by_uid($has_oauthed);
                        exit;
                    }

                     $this->load->library('s');
                        $this->s->assign(array(
                            'title' => '使用'.$this->service_map[$this->service].'账号登录',
                            'user_name' => $_SESSION['user_data']['user_name'],
                            'avatar' => $_SESSION['avatar'],
                            'is_unique' => $this->check_user_name($_SESSION['user_data']['user_name'])
                        ));
                        $this->s->display('oauth/oauth_confirm.html');
                }
        }
       
    }

    function index()
    {
        show_404();
    }



function create_account()
    {
        $this->load->model('user');
        //get user name
        $user_name = $this->input->post('user_name');
        //check user name
        $is_unique_user_name =$this->check_user_name($user_name);
        if(!$is_unique_user_name){
            die('the username has been used by other users');
        }

        $user_data =$_SESSION['user_data'];
        $user_data['user_name']=$user_name;
        $this->db->insert('user', $user_data);
        $user_id = $this->db->insert_id();


        $oauth= $_SESSION['token_data'];
        $oauth['user_id']=$user_id;

        $this->db->insert('oauth', $oauth);

        if ($this->db->affected_rows() > 0) {
            $this->load->library('FetchAvatar');
            $this->fetchavatar->fetch($_SESSION['avatar'], 20, $user_id, TRUE);
            //login
            $this->user->_set_cookie($user_id, $user_name);
            // destroy session data
            unset($_SESSION['user_data']);
            unset($_SESSION['token_data']);
            unset($_SESSION['avatar']);
            redirect();
        }
    }



    function create_account_from_douban()
    {
        $this->load->model('user');
        $user_name = $this->input->post('user_name');
        $user = array(
            'user_name' => $user_name,
            'user_flag' => 0,
            'user_register_time' => current_time(),
            'user_profile_info' => json_encode($this->user_info_parse())
        );
        $this->db->insert('user', $user);
        $user_id = $this->db->insert_id();

        $oauth = array(
            'o_type' => 'douban',
            'user_id' => $user_id,
            'o_access_token' => $_SESSION['oauth']['access_token'],
            'o_openid' => $_SESSION['oauth']['douban_user_id'],
            'o_refresh_token' => $_SESSION['oauth']['refresh_token'],
            'o_time' => time(),
            'o_expire' => $_SESSION['oauth']['expires_in'],
        );

        $this->db->insert('oauth', $oauth);

        if ($this->db->affected_rows() > 0) {
            $this->load->library('FetchAvatar');
            $this->fetchavatar->fetch($_SESSION['user_info']['avatar'], 20, $user_id, TRUE);
            //login
            $this->user->_set_cookie($user_id, $user_name);
            redirect();
        }
    }

    function bind_account_from_douban()
    {

    }

    function get_apikey()
    {
        return $this->config->item('np.oauth.' . $this->service. '.apikey');
    }

    function get_secret()
    {
        return $this->config->item('np.oauth.' . $this->service . '.secret');
    }



    /**
     * if fails, tell the users what's wrong and try again
     */
    function fail()
    {

    }

    function weibo(){
        
    }

    function qq(){
   /*     $this->load->library('QQ');
        if(!$this->input->get('code')){
            $this->qq->requestAuthorizeCode();
        }
        if($this->uri->segment(3)==='callback'){
            $this->qq->setAuthorizeCode($this->input->get('code'));
            $this->qq->requestAccessToken();
        }*/
    }



    /**
     * douban oauth sign in
     */
    function douban()
    {
        
        /*$apikey = $this->get_apikey('douban');
        $secret = $this->get_secret('douban');

        // no authorizeCode，redirect to authorize
        if (!isset($_GET['code'])) {
            $this->oauth->requestAuthorizeCode();
            exit;
        }
        //callback
        if ($this->uri->segment(3) === 'callback') {
            $this->oauth->setAuthorizeCode($_GET['code']);
            $this->oauth->requestAccessToken();
            //get user info now!
            $_SESSION['user_info'] = $this->user_info = $info = JSON_decode($this->oauth->makeRequest('/v2/user/' . $_SESSION['user_id'], 'GET'), TRUE);
            print_r($info);
            print_r($this->user_info_parse());
            //check oauth
            if ($user_id = $this->check_oauth('douban', $info['id'])) {
                $this->load->model('user');
                $this->user->signin_by_uid($user_id);
            } else { //oauth info not found, then forward
                $_SESSION['location'] = $this->location = $info['loc_name'];
                $this->user_name = $info['uid'];
                $this->avatar = $info['avatar'];
                $this->desc = $info['desc'];
                $this->url = $info['alt'];
                $this->openid = $info['id'];
                // redirect to the user name confirm page
                $this->load->library('s');
                $this->s->assign(array(
                    'title' => '使用豆瓣账号登录',
                    'user_name' => $this->user_name,
                    'avatar' => $this->avatar,
                    'is_unique' => $this->check_user_name($this->user_name)
                ));
                $this->s->display('oauth/oauth_qq.html');
            }
        } else {
            redirect('https://www.douban.com/service/auth2/auth?client_id=' . $appkey . '&redirect_uri=http://nodeprint.com/oauth/douban/callback&response_type=code&
  scope=shuo_basic_r,shuo_basic_w,douban_basic_common');
        }
*/

    }

    function github(){

    }

    function twitter()
    {
    $this->load->library('twitter');
    $this->twitter->setConsumerKey('hZt0xvn0WFM9CckTyWjNJw', 'PPhuALrCVrt9DkSlmU7T9iSBWXYMNUfEVwUMW1IxI');
   $cb=$this->twitter->getInstance();



        if (! isset($_GET['oauth_verifier'])) {
            // gets a request token
            $reply = $cb->oauth_requestToken(array(
                'oauth_callback' => 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
            ));

            // stores it
            $cb->setToken($reply->oauth_token, $reply->oauth_token_secret);
            $_SESSION['oauth_token'] = $reply->oauth_token;
            $_SESSION['oauth_token_secret'] = $reply->oauth_token_secret;

            // gets the authorize screen URL
            $auth_url = $cb->oauth_authorize();
            header('Location: ' . $auth_url);
            die();

        } else {
            // gets the access token
            $cb->setToken($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
            $reply = $cb->oauth_accessToken(array(
                'oauth_verifier' => $_GET['oauth_verifier']
            ));
            $_SESSION['oauth_token'] = $reply->oauth_token;
            $_SESSION['oauth_token_secret'] = $reply->oauth_token_secret;
            print_r($reply);
            //stdClass Object ( [oauth_token] => 122685058-h3NWfe7RCX1uRNEqdtD8TQ5ulRiFKfLnwRznQ2Po [oauth_token_secret] => GRnvxcmrRx3CSagLzzXJdjdwDwVLT0C9W4M171J870 [user_id] => 122685058 [screen_name] => alostcat [httpstatus] => 200 )''
          //  $reply = (array) $cb->statuses_homeTimeline();
           // print_r($reply);
        }


    }

    function merge_info()
    {

    }

    function check_user_name($user_name)
    {
        $this->load->library('form_validation');
        return $this->form_validation->is_unique($user_name, 'user.user_name');
    }

    /**
     * show user's oauth info and choose username
     */
    function join()
    {
        $this->load->library('s');
        $this->s->assign(array(
            'title' => 'oauth login'
        ));
        $this->s->display('oauth/oauth_qq.html');
    }


    /**
     * fetch avatar
     *
     * @param string $url avatar url
     * @param int $user_id user_id
     */
    function fetch_avatar($url, $user_id)
    {
        $this->load->library('FetchAvatar');
        $this->fetch_avatar($url, $user_id);
    }


    /**
     * check if user has finish oauth
     *
     * @param string $type
     * @param mixed $openid
     * @return int
     */
    function check_oauth($type, $openid)
    {
        $rs = $this->db->get_where('oauth', array('o_type' => $type, 'o_openid' => $openid));
        if ($rs->num_rows() > 0) {
            return $rs->row()->user_id;
        } else {
            return 0;
        }
    }

    /**
     * do join
     */
    function do_join()
    {
        $this->load->model('user');
        $user = $this->input->post('user_name');
        $pwd = $this->input->post('user_pwd');
        $auth_user = $this->user->login_user($user, $pwd);
        $user_id = $auth_user[2];
        if ($auth_user[0] === 0) {
            $data = array(
                'o_type' => 'douban',
                'user_id' => $user_id,
                'o_access_token' => '',
                'o_openid' => '',
                'o_refresh_token' => '',
                'o_time' => current_time(),
                'o_expire' => ''
            );
            $this->db->insert('oauth', $data);
        }
    }

    function user_info_parse()
    {
        $info = $_SESSION['user_info'];
        $data = array(
            'from' => 'douban'
        );
        $map = array(
            'location' => array('loc_name'),
            'avatar' => array('avatar'),
            'sign' => array('signature'),
            'intro' => array('desc'),
            'site' => array('alt'),
            'douban' => array('uid'),
            'uid' => array('uid')
        );
        foreach ($map as $key => $val) {
            foreach ($info as $item_key => $item_val) {
                $data[$key] = '';
                if (in_array($item_key, $val)) {
                    $data[$key] = $info[$item_key];
                    break;
                }
            }
        }

        return $data;
    }

}

/* End of file oauth.php */
/* Location: ./application/controllers/oauth.php */