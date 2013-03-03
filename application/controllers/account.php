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
 * Account Controller
 *
 * @package         NodePrint
 * @subpackage      Controller
 * @category        Account Controller
 * @author          airyland <i@mao.li>
 * @link            http://github.com/airyland/nodeprint
 */
class Account extends CI_Controller
{

    protected $is_login;

    /**
    * if it is an ajax request
    */
    private $is_ajax = false;

    function __construct()
    {
        parent::__construct();
        $this->is_login = is_login();
        $this->is_ajax = $this->input->is_ajax_request();
        $this->load->library('s');
        $this->load->model('user');
    }

    function index($action = '')
    {
        global $lang;
        if(in_array($action,array('signin','signup','reset_password','forget_password','email_auth_confirm'))){
            if ($this->is_login)  redirect(base_url());
        }
        switch ($action) {

            /**
             * Sign in page
             */
            case 'signin':
                $this->s->display($this->is_ajax?'account/signin_main.html':'account/signin.html');
                break;

            /**
             * Sign up page
             */
            case 'signup':
                session_start();
                $this->load->helper('captcha');
                $random = get_random_strings(5);
                $_SESSION['captcha'] = strtolower($random);
                $config = array(
                    'word' => $random,
                    'img_path' => 'img/captcha/',
                    'img_url' => base_url() . 'img/captcha/',
                    'font_path' => 'application/font/GOTHIC.TTF',
                    'img_width' => '150',
                    'img_height' => 45,
                    'expiration' => 2
                );
                $this->s->assign(array(
                    'site_name' => $this->configs->get_config_item('name'),
                    'captcha' => create_captcha($config)
                ));
                $this->s->display('account/signup.html');
                break;

            /**
             * email activation
             */
            case 'email_auth_confirm':
                $auth = $this->input->get('auth');
                $check = $this->user->email_confirm($auth);
                if ($check){
                    redirect('/settings');
                }
                    break;

            /**
             * reset password
             */
            case 'reset_password':
                $this->s->assign('title', $lang['reset password']);
                $this->s->display('account/reset_password.html');
                break;

            /**
             * forget password
             */
            case 'forget_password':
                $this->s->assign('title', $lang['find password']);
                $this->s->display('account/forget_password.html');
                break;

            /**
             * delete account
             *
             * @todo delete account
             */
            case 'delete_account':
                show_404();
                break;

            case 'create_oauth_account':
                sesstion_start();
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
                    redirect();
            }

                break;

            /**
             * account default page
             */
            default:
                show_404();
                break;
        }
    }


    function check_user_name($user_name)
    {
        $this->load->library('form_validation');
        return $this->form_validation->is_unique($user_name, 'user.user_name');
    }



}

/* End of file account.php */
/* Location: ./application/controllers/account.php */