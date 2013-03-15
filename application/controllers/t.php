<?php

!defined('BASEPATH') && exit('No direct script access allowed');
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
 * Topic pages Controller
 * @author airyland <i@mao.li>
 */
class T extends CI_Controller {

    /**
     * topic author
     *
     * @var mixed
     */
    private $author;

    /**
     * current page
     *
     * @var int
     */
    private $page;

    /**
     * if user has signed in
     *
     * @var bool
     */
    private $is_login;

    /**
     * topics number for each page
     *
     * @var int
     */
    private $limit;

    /**
     * if the request is ajax
     *
     * @var bool
     */
    private $is_ajax;

    public function __construct() {
        parent::__construct();
        $this->load->model('configs');
        $this->load->library('s');
        $this->author = $this->auth->get_user();
        $this->is_login = $this->auth->is_login();
        $this->page = $this->input->get_page();
        $this->is_ajax = $this->input->is_ajax_request();
    }

    /**
     * redirect /t to /t/recent
     */
    public function index() {
        redirect('/t/recent');
    }

    public function the_post($id, $action = '') {
        $this->load->model(array('post', 'comment'));

        // get mobile 
        function mobile($paras) {
            $info=json_decode($paras['info'],true);
            if(isset($info['mb'])&&$info['mb']!==''){
                return ' via '.$info['mb'];
            }
            return '';
        }
        $this->s->registerPlugin('function', 'mobile', 'mobile');

        if (!$action) {
            if (!$this->page) {
                show_404();
            }

            $topic = $this->post->post_info($id);
            if (!$topic) {
                show_404();
            }

            //if enable local server images upload
            $local_upload = $this->configs->item('local_upload');
            $user = get_user();
            $this->post->get_post_fav_no($id);
            $fav = $this->is_login ? $this->post->check_post_fav($user['user_id'], $id) : FALSE;
            $comments = $this->get_comment($id);
            $this->s->assign(array(
                'title' => $topic['post_title'],
                't' => $topic,
                'cm' => $comments,
                'fav' => $fav,
                'local_upload' => $local_upload,
                'page_bar' => $this->dpagination->page_bar,
                'js' => array('np_comment.js', 'np_topic.js'),
                'plugin_topic_toolbar'=>$this->plugins->trigger('topic_toolbar',$id),
				'single_page'=>$this->dpagination->is_single_page
                    )
            );
            if ($this->is_ajax) {
                $this->s->display('topic/single_topic_main.html');
                exit;
            }
            $this->s->display('topic/single_topic.html');
        } else if ($action === 'edit') {
            $this->auth->check_login();
            $this->load->model('nodes');
            $topic = $this->post->post_info($id);
            $topic_edit_expire = $this->configs->item('topic_edit_expire');
            $diff = time() - strtotime($topic['post_time']);
            $has_expire = $diff > intval($topic_edit_expire) * 60;
            $is_author = $this->author['user_id'] === intval($topic['user_id']);
            if (!$is_author && !$this->auth->is_admin()) {
                die('米有权限哦');
            }
            if ($is_author && $has_expire) {
                die('可编辑时间已经超过了哦，可以联系管理员修改');
            }
            $raw_topic = $this->db->get_where('temp', array('t_type' => 'topic', 't_keyid' => $id));
            if ($raw_topic->num_rows() > 0) {
                $raw_topic_info = $raw_topic->row_array();
                $this->s->assign('ori_topic', $raw_topic_info['t_content']);
                $this->s->assign('ori_topic_exists', TRUE);
            } else {
                $this->s->assign('ori_topic', $topic['post_content']);
                $this->s->assign('ori_topic_exists', FALSE);
            }
            $this->s->assign('title', '编辑帖子');
            $this->s->assign('node', $this->nodes->get_node('test'));
            $this->s->assign('topic', $topic);
            if ($this->is_ajax) {
                $this->s->display('topic/edit_topic_main.html');
                exit;
            }
            $this->s->display('topic/edit_topic.html');
        }
    }

    private function get_comment($id){
        //comments number for each page
        $comment_no = $this->configs->item('comment_no');
        //get comments' order
        $order = $this->input->get('order') ? $this->input->get('order') : 'ASC';
        //get comments' number
        $count = $this->comment->list_comment($id, 0, 'cm_id', $order, $this->page, $comment_no, TRUE);
        $this->load->library('dpagination');
        $this->dpagination->generate($count,$comment_no,$this->page,"/t/{$id}",8);
        return  $this->comment->list_comment($id, 0, 'cm_id', $order, $this->page, $comment_no);
    }

    private function the_list($cat, $key = '') {
        //get topic no
        $this->limit = $this->configs->item('topic_no');

        if (!$this->page || !in_array($cat, array(' ', 'recent', 'changes', 'search'))) {
            show_404();
        }

        $this->load->model('post');
        $this->load->library('dpagination');

        switch ($cat) {
            /**
             * Recent added topics
             * @package topic
             */
            case 'recent':
                $post = $this->post->query_post("page={$this->page}&no={$this->limit}");
                $count_post = $this->post->query_post("count=true");
                $title = '最新主题';
                $this->dpagination->target('/t/recent');
                $template = $this->is_ajax ? 'topic/recent_topic_main.html' : 'topic/recent_topic.html';
                break;
            /**
             * Recent changed topics
             * @package topic
             */
            case 'changes':
                $post = $this->post->query_list("orderby=post_last_comment&page={$this->page}&no={$this->limit}");
                $count_post = $this->post->query_list("count=true");
                $title = '最新更改';
                $this->dpagination->target('/t/changes');
                $template = $this->is_ajax ? 'topic/recent_topic_main.html' : 'topic/recent_topic.html';
                break;
            /**
             * Search results
             * @package topic
             */
            case 'search':
                $key = urldecode($key);
                $count_post = $this->post->search_post($key, 0, 0, TRUE);
                $post = $this->post->search_post($key, $this->page, $this->limit);
                $title = $key . '-帖子搜索';
                $template = $this->is_ajax ? 'topic/search_main.html' : 'topic/search.html';
                break;
        }
        $this->dpagination->generate($count_post,$this->limit,$this->page,'/t/search/' . $key);
        $this->s->assign(array(
            'title' => $title . ' ' . $this->page . '/' . intval($count_post / $this->limit + 1),
            'key' => $key,
            'post' => $post,
            'page_bar' => $this->dpagination->page_bar,
            'count' => $count_post,
            'single_page' => $this->dpagination->is_single_page,
                )
        );
        $this->s->display($template);
    }

}

/* End of file t.php */
/* Location: ./application/controllers/t.php */
