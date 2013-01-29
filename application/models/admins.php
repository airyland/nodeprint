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
 * @license		MIT
 * @link		https://github.com/airyland/nodeprint
 * @version	0.0.5
 */

/**
 * 管理员相关Model
 * @subpackage Model
 */
class Admins extends CI_Model {

    /**
    * backup file dir
    */
    const BACKUP_DES='./np-content/backup/';

    /**
    * backup log file
    */
    const BACKUP_LOG='./np-content/backup/log.php';

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
        $today=date('Y-m-d-H-i-s', time());
        $filename=$today . '-' .get_random_string(20).'.zip';
        $log= $this->get_last_backup_file();
        $has_backup_today=$log.strpos($log,$today);
        if (!$has_backup_today&&$this->configs->item('auto_backup')) {
                $this->load->dbutil();
                $backup = & $this->dbutil->backup();
                $this->load->helper('file');
                write_file(self::BACKUP_DES . $filename, $backup);
                write_file(self::BACKUP_LOG,'<?php $file="'.$filename.'";');
        }
    }

    public function get_last_backup_file(){
        $file='';
        include(self::BACKUP_LOG);
        return $file;
    }
}

/* End of file admins.php */
/* Location: ./application/models/admins.php */