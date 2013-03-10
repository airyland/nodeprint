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


class Oauth extends CI_Controller
{
    /**
     * service name
     * @var string
     */
    protected $service;

    /**
     * allow service
     * @var array
     */
    protected $allow_service = array('douban', 'qq', 'weibo', 'github', 'twitter', 'google', 'create_account', 'bind_account', 'readability');

    /**
     * service map
     * @var array
     */
    public $service_map = array(
        'qq' => 'QQ',
        'douban' => '豆瓣',
        'weibo' => '微博',
        'github' => 'Github',
        'google' => 'Google'
    );

    function __construct()
    {
        parent::__construct();
        session_start();
        $segment = $this->uri->segment(2);
        $this->config->load('oauth');
        if (!$segment) show_404();
        if (!in_array($segment, $this->allow_service)) {
            show_404();
            exit;
        }
        $this->service = $segment;
        if ($segment === 'twitter') {
            $this->twitter();
            exit();
        }
        if ($segment !== 'create_account') {
            include APPPATH . 'libraries/NPauth/strategy/' . $this->service . '/' . $this->service . '.php';
            $service = ucfirst($segment);
            $this->oauth = new $service($this->get_apikey(), $this->get_secret());
            $this->load->model('user');
            // check if code is provided, if not, redirect to get authorized
            if (!$this->input->get('code')) {
                $this->oauth->requestAuthorizeCode();
            }
            // callback handler
            if ($this->uri->segment(3) === 'callback') {
                $this->oauth->setAuthorizeCode($this->input->get('code'));
                $this->oauth->requestAccessToken();
                // get openid
                $openid = $this->oauth->openid;
                // get token info
                $token_info = $this->oauth->token_info;
                // get user info
                $this->oauth->parse_user_data();
                // get oauth bind info
                $has_oauthed = $this->check_oauth($this->service, $openid);

                if ($has_oauthed > 0) {
                    $this->load->model('user');
                    $this->user->signin_by_uid($has_oauthed);
                    exit;
                }

                $this->load->library('s');
                $this->s->assign(array(
                    'title' => '使用' . $this->service_map[$this->service] . '账号登录',
                    'user_name' => $_SESSION['user_data']['user_name'],
                    'avatar' => $_SESSION['avatar'],
                    'is_unique' => $this->check_user_name($_SESSION['user_data']['user_name'])
                ));
                $this->s->display('oauth/oauth_confirm.html');
            }
        } else if ($segment === 'create_account') {
            $this->create_account();
        } else if ($segment === 'bind_account') {
            $this->bind_account();
        }

    }

    public function index()
    {
        show_404();
    }

    public function _remap()
    {

    }

    public function create_account()
    {
        $this->load->model('user');
        //get user name
        $user_name = $this->input->post('user_name');
        // no user name posted
        if (!$user_name) die('no username specified');
        //check user name
        $is_unique_user_name = $this->check_user_name($user_name);
        // username already taken by others
        // @todo redirect back to fill another username
        if (!$is_unique_user_name) {
            die('the username has been used by other users');
        }

        // insert into user table
        $user_data = $_SESSION['user_data'];
        $user_data['user_name'] = $user_name;
        // create email confrim code
        $user_data['user_email_confirm'] = get_random_string(16);
        $this->db->insert('user', $user_data);
        $user_id = $this->db->insert_id();

        // add oauth info
        $oauth = $_SESSION['token_data'];
        $oauth['user_id'] = $user_id;
        $this->db->insert('oauth', $oauth);

        // fetch avatar
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

    /**
     * bind account
     * @todo one user cannot bind the same service twice
     *
     */
    public function bind_account()
    {
        $oauth_data = $_SESSION['token_data'];
        $auth_user = $this->auth->get_user();
        $oauth_data['user_id'] = $auth_user['user_id'];
        $this->db->insert('oauth', $oauth);
        // here we do not fetch avatar, but give the option of using which service's avatar
        unset($_SESSION['user_data']);
        unset($_SESSION['token_data']);
        unset($_SESSION['avatar']);
        redirect();
    }


    /**
     * get api key
     * @return string
     */
    private function get_apikey()
    {
        return $this->config->item('np.oauth.' . $this->service . '.apikey');
    }

    /**
     * get secret key
     * @return string
     */
    private function get_secret()
    {
        return $this->config->item('np.oauth.' . $this->service . '.secret');
    }

    /**
     * if fails, tell the users what's wrong and try again
     * @todo
     */
    public function fail()
    {

    }

    function twitter()
    {
        $this->load->library('NPauth/strategy/twitter/twitter');
        $this->twitter->setConsumerKey('hZt0xvn0WFM9CckTyWjNJw', 'PPhuALrCVrt9DkSlmU7T9iSBWXYMNUfEVwUMW1IxI');
        $cb = $this->twitter->getInstance();

        if (!isset($_GET['oauth_verifier'])) {
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
            //stdClass Object ( [oauth_token] => 122685058-h3NWfe7RCX1uRNEqdtD8TQ5ulRiFKfLnwRznQ2Po [oauth_token_secret] => GRnvxcmrRx3CSagLzzXJdjdwDwVLT0C9W4M171J870 [user_id] => 122685058 [screen_name] => alostcat [httpstatus] => 200 )''
            //  $reply = (array) $cb->statuses_homeTimeline();
            // print_r($reply);
        }
    }

    function readibility()
    {
        //https://github.com/kanedo/Readability-API
    }

    private function check_user_name($user_name)
    {
        $this->load->library('form_validation');
        return $this->form_validation->is_unique($user_name, 'user.user_name');
    }


    /**
     * fetch avatar
     *
     * @param string $url avatar url
     * @param int $user_id user_id
     */
    private function fetch_avatar($url, $user_id)
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
    private function check_oauth($type, $openid)
    {
        $rs = $this->db->get_where('oauth', array('o_type' => $type, 'o_openid' => $openid));
        if ($rs->num_rows() > 0) {
            return $rs->row()->user_id;
        } else {
            return 0;
        }
    }
}

/* End of file oauth.php */
/* Location: ./application/controllers/oauth.php */