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
 * 评论 Model
 * @subpackage Model
 */
class Comment extends CI_Model {

    function __construct() {
        parent::__construct();
    }

    /**
     * 添加评论
     * 
     * @access public
     * @return int
     */
    public function add_comment($cm_content, $post_id, $user_id, $user_name, $cm_reply_to = 0, $cm_reply_name = '', $cm_reply_id = 0, $cm_other = '') {
        if (!$post_id)
            return -1;

        if (!$user_id)
            return -2;

        $data = array(
            'cm_content' => $cm_content,
            'user_id' => $user_id,
            'user_name' => $user_name,
            'cm_reply_to' => $cm_reply_to,
            'cm_time' => current_time(),
            'post_id' => $post_id,
            'cm_reply_name' => $cm_reply_name,
            'cm_reply_id' => $cm_reply_id,
            'cm_other' => $cm_other
        );
        $this->db->insert('vx_comment', $data);
        $no = $this->db->insert_id();
        $this->db->set('post_comment_no', 'post_comment_no+1', FALSE)
                ->set('post_last_comment', current_time())
                ->set('post_last_comment_author', $user_name)
                ->where('post_id', $post_id)
                ->update('vx_post');
        return $no;
    }

    /**
     * 删除评论
     * @param int     $id        评论id
     * @param boolean $by_post   是否根据帖子id删除评论
     * @return void
     */
    public function del_comment($id, $by_post = FALSE) {
        $field = ($by_post == FALSE) ? 'cm_id' : 'post_id';
        return $this->db->where($field, $id)->delete('vx_comment');
    }

    /**
     * 获取评论
     * @param int $post_id 帖子id
     * @param int $user_id 可选 用户id
     * @param string $order_by 排序字段
     * @param string $order 排序方式
     * @param int $page 分页页数
     * @param int $no  分页条数
     * @return array|0
     */
    public function list_comment($post_id, $user_id = 0, $order_by = 'cm_id', $order = 'DESC', $page = 1, $no = 50) {
        $this->db->where('post_id', $post_id);
        if ($user_id)
            $this->db->where('user_id', $user_id);
        $rs = $this->db->order_by($order_by, $order)
                ->limit($no, count_offset($page, $no))
                ->get('vx_comment');
        return $rs->num_rows() > 0 ? $rs->result_array() : 0;
    }

}

/* End of file comment.php */
/* Location: ./application/models/comment.php */