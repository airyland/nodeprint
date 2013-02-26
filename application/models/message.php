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
 * Message Model
 * @author airyland <i@mao.li>
 */
class Message extends CI_Model {
    const MSG_TABLE = 'message';

    function __construct() {
        parent::__construct();
    }

    /**
     * send a message
     * @param int $m_type 
     * @return void
     */
    function send_message($m_type, $toname, $fromname, $content, $post_id=0,$cm_id=0) {
        $subject['id'] = '';
        $subject['title'] = '';
        $subject['content'] = $content;
        if ($post_id) {
            $post_title = $this->db->select('post_title')->where('post_id', $post_id)->get('vx_post')->row()->post_title;
            $subject['id'] = $post_id;
            $subject['title'] = $post_title;
        }
        $subject['time'] = current_time();
        //if($m_type===2){
            $subject['cm_id']=$cm_id;
       // }
        $data = array(
            'm_type' => $m_type,
            'm_to_username' => $toname,
            'm_from_username' => $fromname,
            'm_subject' => json_encode($subject),
            'm_read' => 1,
            'm_time' => current_time()
        );
        $this->db->insert(self::MSG_TABLE, $data);
    }

    /**
     * delete a message
     * @param int $m_id
     * @return void
     */
    function del_message($m_id) {
        return $this->db->where('m_id', $m_id)->delete(self::MSG_TABLE);
    }

    /**
     * list message by user
     * @param string $user
     * @param string $user_type 'm_to_username' or 'm_from_username'
     * @param int $read
     * @param int $page
     * @param int $no
     * @param int $count
     * @param int $m_type
     * @param string $start_time
     * @return mixed
     */
    function list_message($user, $user_type='m_to_username', $read=1, $page=1, $no=20, $count=0,$m_type=0,$start_time=null) {
        $this->db->where($user_type, $user);
        
        //private message
        if($m_type==4)
            $this->db->where('m_type', 4);
        
         //for received message, $m_read is ignored
        if($user_type=='m_to_username'&&$read!=-1){
            $this->db->where('m_read', $read);
        }
       
       //count only
        if ($count){
         $new=0;
        $all=$this->db->from(self::MSG_TABLE)->count_all_results();
       //if $start_time given, then get the messages received after the $start_time
        if($start_time){
            $this->db->where('m_time >=',$start_time);
            $new =$this->db->from(self::MSG_TABLE)->count_all_results();
             return array($all,$new,$start_time);
        }
          return $all; 
        }

        $this->db->order_by('m_id', 'DESC')->limit($no, count_offset($page, $no));
        $message = $this->db->get(self::MSG_TABLE);
        $rs = $message->result_array();
        foreach ($rs as $k => $v) {
            $rs[$k]['sub'] = json_decode($v['m_subject'], true);
            unset($rs[$k]['m_subject']);
        }
        return $rs;
    }

    /**
     * set the message status to read
     * @param int $id
     * @param string $type
     */
    function set_read($id=0, $type='message') {
        $user=$this->auth->get_user();
        if ($type == 'message')
            $this->db->where('m_id', $id);
        if ($type == 'post')
            $this->db->where('post_id', $id);
        if ($type== 'setallread')
            $this->db->where_in('m_type',array('1','2','3','4'));
        $this->db->set('m_read', '0')->where('m_to_username',$user['user_name'])->update(self::MSG_TABLE);
    }
}

/* End of file message.php */
/* Location: ./application/models/message.php */