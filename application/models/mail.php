<?php !defined('BASEPATH') && exit('No direct script access allowed');

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
 * 邮件发送
 * @author airyland <i@mao.li>
 */
class Mail extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->library('email');
        $this->load->model('user');
        include(APPPATH . 'config/email.php');
        $this->email->initialize($config);
        $this->email->set_newline("\r\n");
    }

    /**
     * smtp设置保存到文件
     *
     */
    function save_setting($host, $port, $user, $pwd) {
        $config = array(
            'smtp_host' => $host,
            'smtp_user' => $user,
            'smtp_pass' => $pwd,
            'smtp_port' => $port
        );
        file_put_contents(APPPATH . 'config/email.php', '<?php $config=' . var_export($config, true));
    }

    /**
     * 发送邮件
     *
     */
    function send($user_id) {
        $user=$this->get_user_info($user_id);
        $this->email->clear();
        $this->email->from('admin@nodeprint.com', 'NodePrint');
        $this->email->to($user['user_email']);
        $this->email->subject('NodePrint:账号激活邮件');
        
        $this->email->message($this->parse_template($user['user_name'],$user['user_email_confirm']));
        
        if (!$this->email->send()) {
            echo $this->email->print_debugger();
            return FALSE;
        } else {
            //更新状态，表示已发送邮件。
            return TRUE;
        }
    }

    /**
     * send email confirm 
     * 
     */
    function send_email_confirm($limit = 1) {
        $unsent = $this->db->where('user_email_confirm_sent', 0)->where('user_email_confirm !=', '1')->limit($limit)->get('vx_user');
        if ($unsent->num_rows() > 0) {
            foreach ($unsent->result_array() as $list) {
                $this->send($list['user_email'], '', $this->get_email_template(), $this->get_email_template());
            }
        }
    }

    function get_email_template() {
        return file_get_contents(APPPATH . 'templates/email_template.html');
    }

    /**
     * 解析邮件HTML
     * 
     */
    function parse_template($user_name,$user_code) {
        $html = $this->get_email_template();

        
        $base_url = base_url();
        $html=str_replace('$base_url$',$base_url,$html);
        $html=str_replace('$user_name$',$user_name,$html);
        $html=str_replace('$code$',$user_code,$html);
       return $html;
    }
    
    function get_user_info($user_id){
          return $this->user->get_user_profile($user_id,'user_id');
    }

    /**
     * sending log
     *
     */
    function log() {
        
    }

    /**
     * cron job
     *
     */
    function cron() {
        
    }

}

/* End of file mail.php */
/* Location: ./application/models/mail.php */