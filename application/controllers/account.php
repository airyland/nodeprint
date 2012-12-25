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
 * @license		MIT License
 * @link		http://github.com/airyland/nodeprint
 * @version            0.0.5
 */

/**
 * Account Controller
 *
 * 用户账号相关管理器
 *
 * @package        NodePrint
 * @subpackage 	Controller
 * @category	    Account Controller
 * @author		    airyland <i@mao.li>
 * @link 		    http://github.com/airyland/nodeprint
 */
class Account extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('s');
    }

    function index($action = '') {
        $lang = load_lang();
        switch ($action) {
            /**
             * 登录页面
             */
            case 'signin':
                //if ($this->auth->check_login()) {
                   // redirect('/index');
                   // exit;
                //}
                $this->load->model('user');
                $this->s->assign(array(
                    'title' => $lang['signin'],
                    'lang' => $lang,
                ));
                $this->s->display('login.html'); 
                break;
            /**
             * 注册页面
             */
            case 'signup':
                session_start();
                $this->load->helper('captcha');
                $random = get_random_strings(5);
                $_SESSION['captcha'] = strtolower($random);
                $vals = array(
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
                    'site_name' => read_config('name'),
                    'captcha' => create_captcha($vals)
                ));
                $this->s->display('signup.html');
                break;
            /**
             * 邮箱地址激活
             */
            case 'email_auth_confirm':
                $this->load->model('user');
                $auth = $this->input->get('auth');
                $check = $this->user->email_confirm($auth);
                if ($check)
                    break;
            /**
             * 重设密码
             */
            case 'reset_password':
                break;
            /**
             * 忘记密码
             */
            case 'forget_password':
                $this->s->assign('title', '找回密码');
                $this->s->display('forget_password.html');
                break;
            /**
             * 注销账号
             * @note 评论需要删除还是像豆瓣一样显示“用户已注销”?
             */
            case 'delete_account':
                show_404();
                break;
            /**
             * 默认页面
             */
            default:
                show_404();
                break;
        }
    }

}