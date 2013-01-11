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
 * 节点信息
 * @subpackage Controller
 */
class Node extends CI_Controller {

    public $slug;
    public $page;

    function __construct() {
        parent::__construct();
        $this->load->library('s');
    }

    /**
     * 所有节点列表
     * @url /node/
     */
    function index() {
        $this->load->model('nodes');
        $this->s->assign(array(
            'title' => 'Nodes',
            'nodes' => $this->nodes->get_all_nodes()
        ));
        $this->s->display('nodes.html');
    }

    /**
     * 节点介绍及帖子列表
     * @link /node/node_slug 
     * @param string $slug
     */
    function the_node($slug) {
        $action=$this->uri->segment(3);
        if($action==='feed'){
            $this->load->model('feeds');
            $this->feeds->node_feed($slug);
            exit();
        }
        $page = $this->input->get('page') ? $this->input->get('page') : 1;
        if (!is_numeric($page))
            show_error('抱歉，页面不存在', 404);
        $this->load->model('nodes');
        $this->load->model('post');
        $this->load->model('follow');
        $user = get_user();
        $node = $this->nodes->get_node($slug);
        if (!$node)
            show_error('抱歉，节点尚未创建', 404);
        $limit = 20;

        $this->load->library('dpagination');
        $this->dpagination->items($node['node_post_count']);
        $this->dpagination->limit($limit);
        $this->dpagination->currentPage($page);
        $this->dpagination->target('/node/' . $node['node_slug']);
        $this->dpagination->adjacents(8);
        $pagebar = $this->dpagination->getOutput();

        $fav = $this->follow->check_follow($user['user_id'], $slug, $field = 'f_keyname', 2);
        $this->s->assign(array(
            'title' => $node['node_name'] . ' ' . $page . '/' . intval($node['node_post_count'] / 20 + 1) . ' ',
            'node' => $node,
            'post' => $this->post->query_post('node_id=' . $node['node_id'] . 'no=20'),
            'showPageBar' => $node['node_post_count'] > 0 ? true : false,
            'page_bar' => $pagebar,
            'fav' => $fav
        ));
        $this->s->display('node_info.html');
    }

    /**
     * 发表帖子
     * @url /node/node_slug/add
     * @param string $slug
     */
    function add_post($slug) {
        $this->auth->check_login();
        $this->load->model('nodes');
        $this->s->assign('title', '创建帖子');
        $this->s->assign('node', $this->nodes->get_node($slug));
        $this->s->display('topic/add_post.html');
    }

    /**
     * 加载节点自定义样式
     * @param string  $slug
     * @todo 待支持
     */
    function load_css() {
        
    }

}

/* End of file post.php */
/* Location: ./application/controllers/node.php */
