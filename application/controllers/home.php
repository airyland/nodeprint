<?php

!defined('BASEPATH') && exit('No direct script access allowed');

/**
 * Home Controller
 * 
 * @subpackage  Controller
 */
class Home extends CI_Controller {
    /**
     * current tab 
     * @var string
     */
    private $tab;
    
    /**
     * available tabs
     * @var array
     */
    private $available_tab=array('all','following-node','following-member');
    
    /** 
     * topics of current tab
     * @var array
     */
    private $topics;
    
    /**
     * current user
     * @var mixed
     */
    private $curr_user;
    
    /**
     * topic limit
     * @var int
     */
    private $limit;
    
    /**
     * sidebar::new nodes display no
     * @var int
     */
    private $new_nodes_item_no;
    
    /**sidebar::hot nodes display no
     * @var int
     */
    private $hot_nodes_item_no;
    
    /**
     * if show status
     * @var bool
     */
    private $show_status;

	/**
	* if is ajax request
	* @var bool
	*/
    private $is_ajax;

    /**
     * Home constructor
     * 
     * initialize, get topics of current tab
     */
    function __construct() {
        parent::__construct();
        $this->tab = $this->input->get('tab');
        // check if the tab is available
        if($this->tab){
            if(!in_array($this->tab,$this->available_tab)){
                show_404();
            }
        }
        $this->config->load('site');
        $this->load->library('s');
        $this->load->model(array('post','nodes','site','user'));
        
        $this->limit = $this->configs->get_config_item('topic_no');
        $this->hot_nodes_item_no=$this->config->item('np.node.hot_nodes_no');
        $this->new_nodes_item_no=$this->config->item('np.node.new_nodes_no');
        $this->show_status=$this->configs->get_config_item('show_status');
		$this->is_ajax=$this->input->is_ajax_request();
        $this->curr_user = $this->auth->get_user();
    }


    /**
     * Home Controller
     */
    public function index() {
        global $lang;
        if (!$this->s->isCached("index.html")) {
            $this->s->assign(array(
                'title' => $lang['index'],          
                'post' => $this->get_topic(),
                'nodes' => $this->nodes->get_all_nodes(TRUE),
                'show_status'=>$this->show_status,
                'status' => $this->site->get_site_status(),
                'hot_nodes' => $this->nodes->list_node(2, 0, 'node_post_no', 'DESC', 1, $this->new_nodes_item_no),
                'lates_nodes' => $this->nodes->list_node(2, 0, 'node_id', 'DESC', 1, $this->new_nodes_item_no),
                'ad' => ''
                    )
            );
        }
        if($this->is_ajax){
	        $this->s->display('home/main.html');
	        exit;
        }
        $this->s->display('home/index.html');
    }


    /**
    * query topics of the tab
    * @access public
    */
    public function get_topic(){
        if($this->tab&&$this->tab!=='all'){
            switch($this->tab){
                case 'following-member':
                    $this->load->model('follow');
                    $this->topics = $this->follow->get_following_user_stream($this->curr_user['user_id'], false, 1, $this->limit);
                break;

                case 'following-node':
                $this->load->model('nodes');
                $following_nodes = $this->user->get_user_fav_node($this->curr_user['user_id']);
                $this->topics = $this->nodes->get_user_fav_node_post($this->curr_user['user_id'], 1, $this->limit, false, 'post_last_comment');
                $this->s->assign('links',$following_nodes);
                break;
            }
        }else{
            $query_string="order_by=post_last_comment&no={$this->limit}&all=0";
            $this->topics=$this->post->query_post($query_string);
        }
        return $this->topics;
    }

}

/* End of file home.php */
/* Location: ./application/controller/home.php */
