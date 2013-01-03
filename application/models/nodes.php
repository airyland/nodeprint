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
class Nodes extends CI_Model {

    const NODE_TABLE = 'vx_node';
    const CACHE_FILE = 'application/cache/site/node_cache.php';
    const CSS_CACHE = 'application/cache/site/';
    const NODE_HIDE_CACHE_FILE='application/cache/site/node_hide_cache.php';

    function __construct() {
        parent::__construct();
    }

    /**
     * 获得节点信息
     *
     * @access public
     * @param  string  $node_name
     * @param  string  $field  'node_slug'|'node_id'
     * @return mixed
     * @todo 节点名不可重复
     */
    public function get_node($node_name, $field = 'node_slug') {
        $rs = $this->db->where($field, $node_name)->get('vx_node');
        if ($rs->num_rows() > 0) {
            $node_info = $rs->row_array();
            $node_info['node_post_count'] = $this->db->from('vx_post')
                    ->where('node_id', $node_info['node_id'])
                    ->count_all_results();
            return $node_info;
        }
        return 0;
    }

    /**
     * 添加节点
     *
     * @access public
     * @param string $node_type
     * @param string $node_name
     * @param string $node_slug
     * @param int $node_parent
     * @param string $node_intro
     * @return int $node id
     */
    public function add_node($node_type, $node_name, $node_slug, $node_parent, $node_intro) {
        if(!$node_slug||!$node_name){
            return 0;
        }
        $data = array(
            'node_type' => $node_type,
            'node_name' => $node_name,
            'node_slug' => $node_slug == '' ? $node_name : $node_slug,
            'node_parent' => $node_parent,
            'node_related' => '',
            'node_intro' => $node_intro,
            'node_post_no' => 0
        );
        $e = 0;
        if ($this->db->insert('vx_node', $data))
            $e = $this->db->insert_id();
        return $e;
    }

    /**
     * 更新节点
     * @param mixed $node
     * @param string $type
     * @param string $node_intro
     * @param string $node_icon
     * @todo 放弃中
     * @return boolean
     */
    function update_node($node, $type = 'node_id', $node_intro, $node_icon = '') {
        $this->db->where($type, $node)->set('node_intro', $node_intro)->set('node_icon', $node_icon)->update('vx_node');
    }

    function update_node_info($node_id,$data){
        $this->db->update('vx_node',$data,array('node_id'=>$node_id));
        if($this->db->affected_rows()>0){
            return TRUE;
        }
        return FALSE;
    }

    /**
     * 删除节点
     * @param int $node_id
     * @return void
     * @note the posts belonged to the node will be move to defaut node, the defautl node id is 0
     * @todo 删除节点时，相应节点的主题迁移的问题
     */
    function del_node($node_id) {
        $node_info=$this->get_node($node_id,'node_id');
        $this->db->where('node_id', $node_id)->delete('vx_node');
        $this->db->set('node_id', 0)->where('node_id', $node_id)->update('vx_post');
                if($node_info['node_type']===1){
                //更新子节点的父节点id为1
                $this->db->update('vx_nodes',array('node_parent'=>1),array('node_parent'=>$node_id));
        }
    }

    /**
     * list node
     * @access public
     * @param  int     $node_type  
     * @param  int     $node_parent
     * @param  string  $order_by
     * @param  string  $order
     * @param  int     $page
     * @param  int     $no
     * @return mixed
     */
    function list_node($node_type = 2, $node_parent = 0, $order_by = 'node_id', $order = 'DESC', $page = 1, $no = 15) {
        if($node_type){
             $this->db->where('node_type', $node_type);
        }
        if ($node_parent)
            $this->db->where('node_parent', $node_parent);
        $this->db->order_by($order_by, $order)->limit($no, ($page - 1) * $no);
        $rs = $this->db->get('vx_node');
        return $rs->num_rows() > 0 ? $rs->result_array() : 0;
    }

    function refresh_node_post_no() {
        $node = $this->db->select('node_id')->get('vx_node')->result_array();
        foreach ($node as $n) {
            $no = $this->db->from('vx_post')->where('node_id', $n['node_id'])->count_all_results();
            $this->db->set('node_post_no', $no)->where('node_id', $n['node_id'])->update('vx_node');
        }
    }

    /**
     * 用户关注节点帖子
     *
     * @access public
     * @param int $user_id
     * @param int $page
     * @param int $no
     * @param bool $count
     * @return mixed
     */
    public function get_user_fav_node_post($user_id, $page = 1, $no = 20, $count = false,$order_by = 'post_id') {
        $this->db->from('vx_post')
                ->join('vx_follow', 'vx_follow.f_keyid=vx_post.node_id')
                ->join('vx_node', 'vx_post.node_id=vx_node.node_id')
                ->where('vx_follow.f_type', 2)
                ->where('vx_follow.user_id', $user_id);
        if ($count == false) {
            $rs = $this->db->order_by($order_by, 'DESC')->limit($no, count_offset($page, $no))->get();
            return $rs->num_rows() > 0 ? $rs->result_array() : 0;
        } else {
            return $this->db->count_all_results();
        }
    }

    /**
     * save node cache
     * @abstract save node cache to /application/cache/site/node_cache.php
     * include the file when used the cache
     * every time you update the node, call this function to keep the node info updated.
     * @return void
     */
    function save_node_cache() {
        $rs = $this->db->select('node_id,node_name,node_slug,node_post_no')->get('vx_node')->result_array();
        $node = array();
        foreach ($rs as $k => $v) {
            $node[$v['node_id']] = $v;
        }
        file_put_contents(self::CACHE_FILE, '<?php $node=' . "\n" . var_export($node, true) . ';?>');
    }

    /**
     * read node cache
     * @access public
     * @param int $node_id
     * @return array   node info of specified node id
     */
    function read_node_cache($node_id) {
        if (!file_exists(self::CACHE_FILE))
            $this->save_node_cache();
        include(self::CACHE_FILE);
        return $node[$node_id];
    }

    /**
     * save node css of a specified node id
     * @param int $node_id
     * @param string $node_css
     * @return void
     */
    function save_node_css($node_id, $node_css) {
        $this->db->set('node_css', $node_css)
                ->where('node_id', $node_id)
                ->update(self::NODE_TABLE);
        $this->save_node_css_cache($node_id);
    }

    /**
     * save node css cache
     * @param int $node_id
     * @return void
     */
    function save_node_css_cache($node_id) {
        $css = $this->db->select('node_css')
                        ->where('node_id', $node_id)
                        ->get(self::NODE_TABLE)
                        ->row()
                ->node_css;
        file_put_contents(self::CSS_CACHE . $node_id . '.css', $css);
    }

    /**
     * get node  css
     * @param int $node_id
     * @return string 
     *
     */
    function get_node_css($node_id) {
        if (!file_exists(self::CSS_CACHE . $node_id . '.css'))
            $this->save_node_css_cache($node_id);
        return file_get_contents(self::CSS_CACHE . $node_id . '.css');
    }

    /**
     * 添加相关节点，可能会被废弃
     *
     * @param int $node_id
     * @param string $related   node_id or node_slug
     * @param boolean $is_id 
     * @return bool
     */
    function add_node_related($node_id, $related, $is_id = TRUE) {
        $info = $this->get_node($node_id, 'node_id');
        //get related node id
        if (!$is_id) {
            $related_node_id = $this->db->where('node_slug', $related)
                            ->get('vx_node')
                            ->row()
                    ->node_id;
        } else {
            $related_node_id = $related;
        }
        //if has related nodes, check if exists
        if ($info['node_realted']) {
            $o_related_nodes = explode(',', $info['']);
            if (!in_array($related_node_id, $o_related_nodes)) {
                $this->db->update('vx_node', array('node_realted' => $info['node_related'] . ',' . $node_related_id), array('node_id' => $node_id));
            } else {
                return true;
            }
            //no related node, just insert it
        } else {
            $this->db->update('vx_node', array('node_related' => $related_node_id), array('node_id' => $node_id));
        }
    }

    /**
     * 不上首页节点列表
     */
    function get_hide_nodes(){
        $rs=$this->db->get_where(self::NODE_TABLE,array('node_onindex'=>0));
        return $rs->result_array();
    }

    function save_hide_nodes(){
      
            $data=$this->get_hide_nodes();
           $hide_data=array();
       foreach($data as $item){
            array_push($hide_data,$item['node_id']);
        }
        file_put_contents(self::NODE_HIDE_CACHE_FILE, '<?php $hide_nodes='.var_export($hide_data,true).';');      
    }

}

/* End of file nodes.php */
/* Location: ./application/models/nodes.php */
