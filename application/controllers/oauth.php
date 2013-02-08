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
 * @package    NodePrint
 * @author        airyland <i@mao.li>
 * @copyright    Copyright (c) 2012, mao.li.
 * @license        MIT
 * @link        https://github.com/airyland/nodeprint
 * @version    0.0.5
 */
class Oauth extends CI_Controller
{

    /**
     * @var location
     */
    protected $location;
    /**
     * @var url
     */
    protected $url;
    /**
     * @var uid
     */
    protected $uid;
    /**
     * @var username
     */
    protected $user_name;
    /**
     * @var expire time
     */
    protected $expire;
    /**
     * @var token info
     */
    protected $token;
    /**
     * @var desc
     */
    protected $desc;
    /**
     * @var avatar
     */
    protected $avatar;
    /**
     * @var allow types
     */
    protected $allow_types = array('douban', 'qq', 'weibo');


    function __construct()
    {
        parent::__construct();
        session_start();
        $this->load->model('user');
        $this->config->load('oauth');
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

    function bind_account_from_douban(){

    }

    function get_apikey($type)
    {
        return $this->config->item('np.oauth.' . $type . '.apikey');
    }

    function get_secret($type)
    {
        return $this->config->item('np.oauth.' . $type . '.secret');
    }

    function index()
    {

    }

    /**
     * if fails, tell the users what's wrong and try again
     */
    function fail()
    {

    }

    /**
     * douban oauth sign in
     */
    function douban()
    {
        $apikey = $this->get_apikey('douban');
        $secret = $this->get_secret('douban');
        $params = array(
            'type' => 'douban',
            'clientId' => $apikey,
            'secret' => $secret,
            'redirectUri' => 'http://nodeprint.com/oauth/douban/callback',
            'scope' => 'douban_basic_common',
            'responseType' => 'code'
        );
        $this->load->library('oauths', $params);
        // no authorizeCode，redirect to authorize
        if (!isset($_GET['code'])) {
            $this->oauths->requestAuthorizeCode();
            exit;
        }
        //callback
        if ($this->uri->segment(3) === 'callback') {
            $this->oauths->setAuthorizeCode($_GET['code']);
            $this->oauths->requestAccessToken();
            //get user info now!
            $_SESSION['user_info'] = $this->user_info = $info = JSON_decode($this->oauths->makeRequest('/v2/user/' . $_SESSION['user_id'], 'GET'), TRUE);
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
                //add recored to oauth table
                //fetch avatar
                //login user
            }
        } else {
            redirect('https://www.douban.com/service/auth2/auth?client_id=' . $appkey . '&redirect_uri=http://nodeprint.com/oauth/douban/callback&response_type=code&
  scope=shuo_basic_r,shuo_basic_w,douban_basic_common');
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
/* Location: ./application/controllers.oauth.php */