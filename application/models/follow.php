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
 * follow model
 * @note this includes follow members, fav topics, fav,
 */
class Follow extends CI_Model {

    function __construct() {
        parent::__construct();
    }
    
    /**
     * add follow
     * @param int $user_id
     * @param int $type 1|2|3
     * @param int $keyid
     * @param string $keyname
     * @return void
     */
    function add_follow($user_id, $type, $keyid, $keyname) {
        $check = $this->db->where('user_id', $user_id)
                ->where('f_type', $type)
                ->where('f_keyid', $keyid)
                ->get('vx_follow')
                ->num_rows();
        if ($check == 0) {
            $data = array(
                'user_id' => $user_id,
                'f_type' => $type,
                'f_keyid' => $keyid,
                'f_keyname' => $keyname,
                'f_subject'=>''
            );
            $this->db->insert('vx_follow', $data);
        }
        
    }
    /**
     * check follow
     * @param int $user_id
     * @param int $key
     * @param string $field
     * @param int $type
     * @return boolean 
     */
    function check_follow($user_id, $key, $field='f_keyid', $type=1) {

        $rs = $this->db->where('user_id', $user_id)
                ->where($field, $key)
                ->where('f_type', $type)
                ->get('vx_follow')
                ->num_rows();
        return $rs > 0 ? TRUE : FALSE;
    }
    
    /**
     * delete a follow
     * @param int $user_id
     * @param string $type
     * @param int $keyid 
     * @return void
     */
    function del_follow($user_id, $type, $keyid) {
        $where = array(
            'user_id' => $user_id,
            'f_keyid' => $keyid,
            'f_type' => $type
        );
        $this->db->where($where)->delete('vx_follow');
    }
    
    /**
     *
     * @param int $user_id
     * @param string $user_type
     * @param int $type
     * @param int $page
     * @param int $no
     * @return array|0
     */
    function list_follow($user_id, $user_type='user_id', $type=1, $page=1, $no=20) {
        if ($user_type == 'user_name') {
            $user_id = $this->db->select('user_id')->where('user_name', $user_id)->get('vx_user')->row()->user_id;
        }
        $rs = $this->db->select('vx_post.user_id,post_id,post_title,post_time,post_comment_no,post_last_comment_author,node_name,node_slug,post_hit,post_last_comment,user_name')
                ->from('vx_follow')
                ->where('vx_follow.user_id', $user_id)
                ->where('f_type', $type)
                ->join('vx_post', 'vx_post.post_id=vx_follow.f_keyid')
                ->join('vx_node','vx_post.node_id=vx_node.node_id')
                ->limit($no, count_offset($page, $no))
                ->get();
        return $rs->num_rows() > 0 ? $rs->result_array() : 0;
    }


    function count_fav($user, $type) {
        $user_id = $this->db->select('user_id')->where('user_name', $user)->get('vx_user')->row()->user_id;
        return $this->db->from('vx_follow')->where('user_id', $user_id)->where('f_type', $type)->count_all_results();
    }

    function get_following_user_stream($user_id,$count=false,$page=1,$no=20){
       $this->db->from('vx_follow')
        ->where('vx_follow.user_id',$user_id)
        ->where('vx_follow.f_type',3)
        ->join('vx_post','vx_post.user_id=vx_follow.f_keyid');
       
        if($count) return $this->db->count_all_results();
        if($page) $this->db->join('vx_node','vx_post.node_id=vx_node.node_id')
            ->order_by('post_id','DESC')
            ->limit($no,count_offset($page,$no));
        $rs=$this->db->get();
        return $rs->num_rows()>0?$rs->result_array():0;
    }

}
/* End of file follow.php */
/* Location: ./application/models/follow.php */