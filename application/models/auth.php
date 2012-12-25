<?php

!defined('BASEPATH') && exit('No direct script access allowed');

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

/**
 * 用户验证 Model
 * 
 * @subpackage Model
 */
class Auth extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    /**
     * 检查是否已经登录
     * 
     * @access public
     */
    public function check_login() {
        $user = $this->get_user();
        if ($user['error'] == 2) {
            if ($this->input->is_ajax_request()) {
                echo json_encode(array('error' => -1, 'msg' => 'not logined yet'));
                exit;
            } else {
                header("location:" . base_url() . 'signin?from=');
                exit();
            }
        }
    }

    /**
     * 从cookie获取用户资料
     * 
     * @access public
     * @return array
     */
    public function get_user() {
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
     * 检查是否有管理员权限
     * 
     * @access public
     * @return boolean
     */
    public function is_admin() {
        $user = $this->get_user();
        if ($user['error'] === 1) {
            return $this->db->where('user_id', $user['user_id'])
                            ->where('user_flag', 9)
                            ->get('vx_user')
                            ->num_rows();
        }
        return FALSE;
    }

    /**
     * 进入需要管理员权限的页面时，如果没有登录则跳转到登录页面
     * 
     * @access public
     */
    public function check_admin() {
        if (!$this->is_admin()) {
            redirect('/signin#no-admin-rights');
        }
    }

}

/* End of file auth.php */
/* Location: ./application/models/auth.php */