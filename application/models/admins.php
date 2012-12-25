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
 * 管理员相关Model
 * @subpackage Model
 */
class Admins extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->auth->check_admin();
    }

    /**
     * 添加管理员
     * 
     * @access public
     * @param string $user_name
     * @return array
     */
    public function add_admin($user_name) {
        $rs = $this->db->get_where('vx_user', array('user_name' => $user_name));
        if ($rs->num_rows() === 0) {
            return array('error' => 1, 'msg' => '用户不存在');
        } else {
            $user = $rs->row();
            if ($user->user_flag === '9') {
                return array('error' => 2, 'msg' => '该用户已经是管理员');
            } else {
                $this->db->update('vx_user', array('user_flag' => 9), array('user_name' => $user_name));
                return array('error' => 0, 'msg' => '添加管理员成功');
            }
        }
    }

    /**
     * 获取所有管理员
     *
     * @return array
     */
    public function get_admin() {
        return $this->db->select('user_id,user_name')->where(array('user_flag' => 9))->get('vx_user')->result_array();
    }

    /**
     * 每天备份一次，在管理员后台可关闭
     */
    public function backup() {
        if ($this->configs->get_config_item('auto_backup')) {
            if (!file_exists(APPPATH . 'backup/' . date('Y-m-d', time()) . '.zip')) {
                $this->load->dbutil();
                $backup = & $this->dbutil->backup();
                $this->load->helper('file');
                write_file(APPPATH . 'backup/' . date('Y-m-d', time()) . '.zip', $backup);
            }
        }
    }
}

/* End of file admins.php */
/* Location: ./application/models/admins.php */