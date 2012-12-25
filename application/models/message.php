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
 * @license		GNU General Public License 2.0
 * @link		https://github.com/airyland/nodeprint
 * @version	0.0.5
 */
/**
 * Message Model
 * @author airyland <i@mao.li>
 */
class Message extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    /**
     * send a message
     * @param int $m_type 
     * @return void
     */
    function send_message($m_type, $toname, $fromname, $content, $post_id=0) {
        $subject['id'] = '';
        $subject['title'] = '';
        $subject['content'] = $content;
        if ($post_id) {
            $post_title = $this->db->select('post_title')->where('post_id', $post_id)->get('vx_post')->row()->post_title;
            $subject['id'] = $post_id;
            $subject['title'] = $post_title;
        }
        $subject['time'] = current_time();
        $data = array(
            'm_type' => $m_type,
            'm_to_username' => $toname,
            'm_from_username' => $fromname,
            'm_subject' => json_encode($subject),
            'm_read' => 1
        );
        $this->db->insert('vx_message', $data);
    }

    /**
     * delete a message
     * @param int $m_id
     * @return void
     */
    function del_message($m_id) {
        return $this->db->where('m_id', $m_id)->delete('vx_message');
    }

    /**
     * list message by user
     * @param string $user
     * @param string $user_type m_to_username or m_from_username
     * @param int $read
     * @param int $page
     * @param int $no
     * @param int $count
     * @return array|0 
     */
    function list_message($user, $user_type='m_to_username', $read=1, $page=1, $no=20, $count=0,$m_type=0) {
        $this->db->where($user_type, $user);
        
        //收到的私信
        if($m_type==4)
            $this->db->where('m_type', 4);
        
         //若为发送信息，则不分是否已读
        if($user_type=='m_to_username'){
             if ($read!=-1) $this->db->where('m_read', $read);
        }
        
       //只计数
        if ($count)
            return $this->db->from('vx_message')->count_all_results();

        $this->db->order_by('m_id', 'DESC')->limit($no, count_offset($page, $no));
        $message = $this->db->get('vx_message');
        $rs = $message->result_array();
        foreach ($rs as $k => $v) {
            $rs[$k]['sub'] = json_decode($v['m_subject'], true);
            unset($rs[$k]['m_subject']);
        }
        return $rs;
    }

    /**
     * set the message status to read
     * @param type $id
     * @param type $type 
     */
    function set_read($id=0, $type='message') {
        $user=$this->auth->get_user();
        if ($type == 'message')
            $this->db->where('m_id', $id);
        if ($type == 'post')
            $this->db->where('post_id', $id);
        if ($type== 'setallread')
            $this->db->where_in('m_type',array('1','2','3','4'));
        $this->db->set('m_read', '0')->where('m_to_username',$user['user_name'])->update('vx_message');
    }
}

/* End of file message.php */
/* Location: ./application/models/message.php */