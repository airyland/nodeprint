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
    }

    /**
     * add an admin
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
     * get all admins
     *
     * @return array
     */
    public function get_admin() {
        return $this->db->select('user_id,user_name')
                        ->where(array('user_flag' => 9))
                        ->get('user')
                        ->result_array();
    }

    /**
     * auto backup database
     * can be shut down on admin dashboard
     */
    public function auto_backup() {
        $filename=$this->get_filename();
        $log= $this->get_last_backup_file();
        $has_backup_today=strpos($log,date('Y-m-d',time()))===0;
        if (!$has_backup_today&&$this->configs->item('auto_backup')) {
            $this->backup($filename);
        }
    }

    /**
    * manual backup database
    */
    public function manual_backup(){
        $this->auth->check_admin();
        $filename=$this->get_filename();
        $this->backup($filename);
    }

    /**
    * get backup file name
    */
    private function get_filename(){
        $today=date('Y-m-d-H-i-s', time());
        return $today . '-' .get_random_string(20).'.zip';
    }


    /**
    * do the backup job
    */
    private function backup($filename){
         $this->load->dbutil();
         $backup = & $this->dbutil->backup();
         $this->load->helper('file');
         write_file(self::BACKUP_DES . $filename, $backup);
         write_file(self::BACKUP_LOG,'<?php $file="'.$filename.'";');
    }

    /**
    * get latest backup time
    */
    public function get_last_backup_file(){
        $file='';
        if(file_exists(self::BACKUP_LOG)){
            include(self::BACKUP_LOG);
        }
        return $file;
    }
}

/* End of file admins.php */
/* Location: ./application/models/admins.php */