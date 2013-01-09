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
 * @license		MIT
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
    private $available_tab=array('following-node','following-member');
    public $topics;
    private $curr_user;
    public $limit;
    public $count;
    private $new_nodes_item_no;
    private $hot_nodes_item_no;

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
        $this->config->load('site');
        $this->load->model('user');
        $this->limit = $this->configs->get_config_item('topic_no');
        $this->hot_nodes_item_no=$this->config->item('np.node.hot_nodes_no');
        $this->new_nodes_item_no=$this->config->item('np.node.new_nodes_no');
        $this->curr_user = $this->auth->get_user();
        $this->tab = $this->input->get('tab');
    }

    /**
    * query topics
    *
    */
    public function get_topic(){
        if($this->tab&&in_array($this->tab,$this->available_tab)&&$this->tab!=='all'){
            switch($this->tab){
                case 'following-member':
                    $this->load->model('follow');
                    $this->topics = $this->follow->get_following_user_stream($this->curr_user['user_id'], false, 1, $this->limit);
                break;

                case 'following-node':
                $this->load->model('nodes');
               // $node = $this->user->get_user_fav_node($this->curr_user['user_id']);
                $this->topics = $this->nodes->get_user_fav_node_post($this->curr_user['user_id'], 1, $this->limit, false, 'post_last_comment');
                //$count_post = $this->nodes->get_user_fav_node_post($this->curr_user['user_id'], 0, 0, true);
                break;
            }
        }else{
            $query_string="order_by=post_last_comment&no={$this->limit}&all=0";
            $this->topics=$this->post->query_post($query_string);
        }
        return $this->topics;
    }

    /**
     * 首页默认页面 Controller
     */
    public function index() {
        $lang = load_lang();
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
                'post' => $this->get_topic(),
                'nodes' => $nodes,
                'show_status'=>$show_status,
                'status' => $this->site->get_site_status(),
                'hot_nodes' => $this->nodes->list_node(2, 0, 'node_post_no', 'DESC', 1, $this->new_nodes_item_no),
                'lates_nodes' => $this->nodes->list_node(2, 0, 'node_id', 'DESC', 1, $this->new_nodes_item_no),
                'ad' => ''
                    )
            );
        }
        $this->s->display('index.html');
    }

}

/* End of file home.php */
/* Location: ./application/controller/home.php */
