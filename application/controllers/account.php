<?php

!defined('BASEPATH') && exit('No direct script access allowed');

/**
 * NodePrint
 *
 * 轻论坛程序
 *
 * NodePrint is a lightweight BBS built on Ci.
 *
 * @package            NodePrint
 * @author             airyland <i@mao.li>
 * @copyright         Copyright (c) 2012 , mao.li.
 * @license           MIT License
 * @link                http://github.com/airyland/nodeprint
 * @version            0.0.5
 */

/**
 * Account Controller
 *
 * @package        NodePrint
 * @subpackage     Controller
 * @category        Account Controller
 * @author            airyland <i@mao.li>
 * @link             http://github.com/airyland/nodeprint
 */
class Account extends CI_Controller
{

    private $is_login;

    function __construct()
    {
        parent::__construct();
        $this->is_login = is_login();
        $this->load->library('s');
    }

    function index($action = '')
    {
        global $lang;
        switch ($action) {

            /**
             * Sign in page
             */
            case 'signin':
                if ($this->is_login) {
                    redirect(base_url());
                }
                $this->load->model('user');
                $this->s->assign(array(
                    'title' => $lang['signin'],
                    'lang' => $lang,
                ));
                $this->s->display('login.html');
                break;

            /**
             * Sign up page
             */
            case 'signup':
                if ($this->is_login) {
                    redirect(base_url());
                }
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
                $this->load->model('user');
                $this->s->assign(array(
                    'title' => $lang['signup'],
                    'lang' => $lang,
                    'site_name' => $this->configs->get_config_item('name'),
                    'captcha' => create_captcha($config)
                ));
                $this->s->display('signup.html');
                break;

            /**
             * email activation
             */
            case 'email_auth_confirm':
                $this->load->model('user');
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
                break;

            /**
             * forget password
             */
            case 'forget_password':
                $this->s->assign('title', $lang['find password']);
                $this->s->display('forget_password.html');
                break;

            /**
             * delete account
             *
             * @todo delete account
             */
            case 'delete_account':
                show_404();
                break;

            /**
             * account default page
             */
            default:
                show_404();
                break;
        }
    }

}

/* End of file account.php */
/* Location: ./application/controllers/account.php */