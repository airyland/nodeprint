<?php

!defined('IN_NODEPRINT') && exit('No direct script access allowed');
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
 * Node Controller
 * @subpackage Controller
 */
class Node extends CI_Controller {
    
    /**
     * node slug
     * @var string
     */
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
        global $is_ajax;
        parent::__construct();
        $this->load->library('s');
        $this->load->model('nodes');
        $this->is_login = $this->auth->is_login();
        $this->is_ajax = $is_ajax;
        $this->page = $this->input->get_page();
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
        if ($this->is_ajax) {
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
        $action = $this->uri->segment(3);
        if ($action === 'feed') {
            $this->load->model('feeds');
            $this->feeds->node_feed($slug);
            exit();
        }
        if ($this->page === 0)
            show_404();

        $this->load->model(array('post', 'follow', 'configs'));
        $limit = $this->configs->item('topic_no');
        $user = get_user();

        $node = $this->nodes->get_node($slug);
        if (!$node)
            show_404();

        $this->load->library('dpagination');
        $this->dpagination->generate($node['node_post_count'],$limit,$this->page,'/node/' . $node['node_slug']);

        if ($this->is_login) {
            $fav = $this->follow->check_follow($user['user_id'], $slug, $field = 'f_keyname', 2);
            $this->s->assign('fav', $fav);
        }

        $this->s->assign(array(
            'title' => $node['node_name'] . ' ' . $this->page . '/' . intval($node['node_post_count'] / $limit + 1) . ' ',
            'node' => $node,
            'post' => $this->post->query_post('node_id=' . $node['node_id'] . 'no=' . $limit),
            'single_page' => $this->dpagination->is_single_page,
            'page_bar' => $this->dpagination->page_bar,
            'admin_js' => array('plugin/jquery.jeditable.mini.js', 'np_admin.js')
        ));
        if ($this->is_ajax) {
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
        if ($this->is_ajax) {
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
