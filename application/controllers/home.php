<?php

!defined('BASEPATH') && exit('No direct script access allowed');
/**
 * NodePrint
 *
 * 轻论坛程序
 * 
 * NodePrint is a lightweight BBS built on Ci.
 *
 * @package            NodePrint
 * @author		airyland <i@mao.li>
 * @copyright	        Copyright (c) 2012 , mao.li.
 * @license		GNU General Public License 2.0
 * @link		http://github.com/airyland/nodeprint
 * @version	0.0.5
 */

/**
 * 首页 Controller
 * 
 * @subpackage  Controller
 */
class Home extends CI_Controller {

    public $tab;

    /**
     * 构造器
     * 
     * 初始化,获取内容分类
     */
    function __construct() {
        parent::__construct();
        $this->load->library('s');
        $this->load->model('post');
        $this->load->model('nodes');
        $this->load->model('site');
        $this->load->model('user');
        $this->tab = $this->input->get('tab');
    }

    /**
     * 首页默认页面 Controller
     */
    function index() {
        $lang = load_lang();
        $limit = $this->configs->get_config_item('topic_no');
        $show_status=$this->configs->get_config_item('show_status');
        if (!$this->s->isCached("index.html")) {
            $nodes = $this->nodes->list_node(1, 0, 'node_id', 'DESC', 1, 15);
            if(is_array($nodes)){
                foreach ($nodes as $k => $v) {
                $nodes[$k]['child_node'] = $this->nodes->list_node(2, $nodes[$k]['node_id'], 'node_id', 'DESC', 1, 15);
            }
            }
            $this->s->assign(array(
                'title' => $lang['index'],          
                'post' => $this->post->query_post("order_by=post_last_comment&no={$limit}&all=0"),
                'nodes' => $nodes,
                'show_status'=>$show_status,
                'status' => $this->site->get_site_status(),
                'hot_nodes' => $this->nodes->list_node(2, 0, 'node_post_no', 'DESC', 1, 10),
                'lates_nodes' => $this->nodes->list_node(2, 0, 'node_id', 'DESC', 1, 10),
                'ad' => ''
                    )
            );
        }
        $this->s->display('index.html');
    }

}

/* End of file home.php */
/* Location: ./application/controller/home.php */
