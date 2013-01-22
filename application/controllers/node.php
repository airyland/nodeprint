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
 * Node Controller
 * @subpackage Controller
 */
class Node extends CI_Controller {

    public $slug;
    /**
     * current page
     * @var number
     */
    public $page;

    /**
     * check if the use has signed in
     * @var bool
     */
    protected $is_login;

    /**
     * check if it is an ajax request
     * @var bool
     */
    private $is_ajax;

    function __construct() {
        parent::__construct();
        $this->load->library('s');
        $this->load->model('nodes');
        $this->is_login=$this->auth->is_login();
        $this->is_ajax = $this->input->is_ajax_request();
        $this->page=$this->input->get_page();
    }

    /**
     * nodes list
     * @url /node/
     */
    function index() {
        $this->s->assign(array(
            'title' => 'Nodes',
            'nodes' => $this->nodes->get_all_nodes()
        ));
        if($this->is_ajax){
            $this->s->display('node_list.html');
            exit;
        }
        $this->s->display('nodes.html');
    }

    /**
     * single node controller
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
        if ($this->page===0)
            show_404();

        $this->load->model(array('post','follow','configs'));
        $limit=$this->configs->item('topic_no');
        $user = get_user();

        $node = $this->nodes->get_node($slug);
        if (!$node)
            show_404();

        $this->load->library('dpagination');
        $this->dpagination->items($node['node_post_count']);
        $this->dpagination->limit($limit);
        $this->dpagination->currentPage($this->page);
        $this->dpagination->target('/node/' . $node['node_slug']);
        $this->dpagination->adjacents(8);
        $pagebar = $this->dpagination->getOutput();

        if($this->is_login){
             $fav = $this->follow->check_follow($user['user_id'], $slug, $field = 'f_keyname', 2);
             $this->s->assign('fav',$fav);
        }
       
        $this->s->assign(array(
            'title' => $node['node_name'] . ' ' . $this->page . '/' . intval($node['node_post_count'] / $limit + 1) . ' ',
            'node' => $node,
            'post' => $this->post->query_post('node_id=' . $node['node_id'] . 'no='.$limit),
            'showPageBar' => $node['node_post_count'] > 0 ? true : false,
            'page_bar' => $pagebar
        ));
        if($this->is_ajax){
	        $this->s->display('node/single_node_main.html');
	        exit;
        }
        $this->s->display('node/single_node.html');
    }

    /**
     *  add topic
     * @url /node/node_slug/add
     * @param string $slug
     */
    function add_post($slug) {
        $this->auth->check_login();
        $this->s->assign('title', '创建帖子');
        $this->s->assign('node', $this->nodes->get_node($slug));
        if($this->is_ajax){
	         $this->s->display('topic/add_topic_main.html');
	         exit;
        }
        $this->s->display('topic/add_topic.html');
    }

    /**
     * load custom css
     * @param string  $slug
     * @todo 
     */
    function load_css($slug) {
        
    }

}

/* End of file node.php */
/* Location: ./application/controllers/node.php */
