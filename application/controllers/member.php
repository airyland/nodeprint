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
 * Member Controller
 * @url /member/user_name
 */
class Member extends CI_Controller
{

    /**
     * current page
     *
     * @var int
     */
    private $page;

    /**
     * if is an ajax request
     *
     * @var bool
     */
    private $is_ajax;

    /**
     * topic limit
     *
     * @var int
     */
    private $limit;

    /**
     * user slug
     *
     * @var string
     */
    private $slug;

    /**
     * if pagination contains only 1 page
     *
     * @var boolean
     */
    private $single_page;

    public function __construct()
    {
        parent::__construct();
        $this->page = $this->input->get_page();
        if (!$this->page) {
            show_404();
        }
        $this->limit = $this->configs->get_config_item('topic_no');
        $this->is_ajax = $this->input->is_ajax_request();
        $this->load->model(array('configs', 'user', 'post'));
        $this->load->library(array('dpagination', 's'));
    }

    /**
     * member list
     * @todo members list page
     */
    public function index()
    {
        show_404();
    }

    private function getPagination($count)
    {
        $this->dpagination->generate($count,$this->limit,$this->page,'/member/' . $this->slug . '/' . $this->action,8);
        $this->page_bar = $this->dpagination->page_bar;
        $this->single_page =$this->dpagination->is_single_page;
    }

    /**
     * display page
     * @param array $data
     */
    public function display($data)
    {
        $count_topic = $this->post->query_post($data['count_query_string']);
        $this->s->assign(
            array(
                'title' => $this->slug . $data['title'],
                'post' => $this->post->query_post($data['post_query_string']),
                'single_page' => $this->single_page,
                'page_bar' => $this->getPagination($count_topic, $data['target']),
                'box_title' => $data['title'],
                'template' => $data['template']
            )
        );

        $this->smart_display($data['template']);
    }

    private function smart_display($template, $wrap_template = 'user_wrap')
    {
        if ($this->is_ajax) {
            $this->s->display('user/' . $template . '.html');
        } else {
            $this->s->display('user/' . $wrap_template . '.html');
        }
    }

    function the_member($slug, $action = '')
    {
        $this->slug = $slug;
        $this->action = $action;
        $slug = urldecode($slug);
        $user = $this->auth->get_user();
        $field = (is_numeric($slug)) ? 'user_id' : 'user_name';
        $u = $this->user->get_user_profile($slug, $field);

        // user doesn't exist
        if (!$u) show_404();

        // user cannot access other members' personal data
        if (in_array($action, array('following', 'favnode', 'favtopic'))) {
            if ($u['user_name'] !== $user['user_name']) {
                die('you are not authorized to see the private data');
            }
        }

        $this->s->assign('u', $u);
        switch ($action) {

            /**
             * Topics created by the user
             */
            case 'topic':
                $data = array(
                    'count_query_string' => "user_id={$slug}&user_type=user_name&count=true",
                    'target' => '/topic',
                    'title' => '创建的帖子',
                    'post_query_string' => 'user_id=' . $this->slug . '&user_type=user_name&page=' . $this->page . 'no=' . $this->limit,
                    'template' => 'user_topic'
                );
                $this->display($data);
                break;

            /**
             * fav topic of the user
             */
            case 'favtopic':
                $this->load->model('follow');
                $count_topic = $this->follow->count_fav($slug, 1);
                $this->getPagination($count_topic);
                $this->s->assign(
                    array(
                        'title' => $slug . '收藏的帖子',
                        'post' => $this->follow->list_follow($slug, 'user_name', 1, $this->page, $this->limit),
                        'single_page' => $this->single_page,
                        'page_bar' => $this->page_bar,
                        'box_title' => '收藏的帖子',
                        'template' => 'user_topic'
                    )
                );
                $this->smart_display('user_topic');
                break;

            case 'favnode':
                $this->load->model('nodes');
                $node = $this->user->get_user_fav_node($user['user_id']);
                $post = $this->nodes->get_user_fav_node_post($user['user_id'], $this->page, $this->limit, false);
                $count_post = $this->nodes->get_user_fav_node_post($user['user_id'], 0, 0, true);
                $this->getPagination($count_post);
                $this->s->assign('title', '收藏的节点');
                $this->s->assign('node', $node);
                $this->s->assign('post', $post);
                $this->s->assign('single_page', $this->single_page);
                $this->s->assign('page_bar', $this->page_bar);
                $this->s->assign('show_page_bar', $count_post > 0);
                $this->s->assign('box_title', '收藏的节点');
                $this->s->assign('template', 'user_favnode');
                $this->smart_display('user_favnode');
                break;

            case 'following':
                $this->load->model('follow');
                $fo = $this->user->get_user_following_member($user['user_id']);
                $stream = $this->follow->get_following_user_stream($user['user_id'], false, $this->page, $this->limit);
                $count_post = $this->follow->get_following_user_stream($user['user_id'], true);
                $this->getPagination($count_post);
                $this->s->assign('title', '关注的用户');
                $this->s->assign('post', $stream);
                $this->s->assign('fo', $fo);
                $this->s->assign('single_page', $this->single_page);
                $this->s->assign('page_bar', $this->page_bar);
                $this->s->assign('box_title', '关注的用户');
                $this->s->assign('template', 'user_following');
                $this->smart_display('user_following');
                break;

            case 'send_message':
                $this->s->assign('title', '发送私信');
                $this->s->display('user/user_send_message.html');
                break;

            case 'replies':
                $this->load->model('follow');
                $count_topic = $this->post->list_user_comment_post($slug, 'user_name', $page = 1, $this->limit, TRUE);
                $this->getPagination($count_topic);
                $this->s->assign(
                    array(
                        'title' => $slug . '回复的帖子',
                        'post' => $this->post->list_user_comment_post($slug, 'user_name', $this->page, $this->limit),
                        'single_page' => $this->single_page,
                        'page_bar' => $this->page_bar,
                        'box_title' => '回复的帖子',
                        'template' => 'user_replies'
                    )
                );
                $this->smart_display('user_replies');
                break;

            /**
             * member info
             */
            default:
                $this->load->model(array('post', 'follow'));
                $is_follow = $this->follow->check_follow($user['user_id'], $u['user_name'], 'f_keyname', $type = 3);
                $filed = (is_numeric($slug)) ? 'user_id' : 'user_name';
                $this->s->assign(array(
                    'title' => $u['user_name'],
                    'post' => $this->post->query_post("user_id={$slug}&user_type={$field}&order=post_last_comment&page={$this->page}&no={$this->limit}"),
                    'hiscomment' => $this->post->list_user_comment_post($slug, $filed, 1, $this->limit),
                    'is_follow' => $is_follow
                ));
                $this->smart_display('member_main', 'member');
                break;
        }
    }
}
/* End of file member.php */
/* Location: ./application/controllers/member.php */