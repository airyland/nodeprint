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
 * 用户 Controller
 * @url /member/user_name
 */
class Member extends CI_Controller {

    /**
     * 分页页数
     * @var int
     */
    public $page;

    function __construct() {
        parent::__construct();
        $this->load->model('configs');
        $this->page = $this->input->get_page();
        if (!is_numeric($this->page))
            show_error('页面不存在', 404);
        $this->load->library('dpagination');
    }

    /**
     * member list
     *  
     */
    function index() {
        show_error('页面不存在', 404);
    }

    function the_member($slug, $action = '') {

        $limit = $this->configs->get_config_item('topic_no');
        $slug = urldecode($slug);
        $this->load->model('user');
        $this->load->model('post');
        $this->load->library('s');

        $user = $this->auth->get_user();
        $field = (is_numeric($slug)) ? 'user_id' : 'user_name';
        $u = $this->user->get_user_profile($slug, $field);
        if (!$u)
            show_error('抱歉，用户不存在', 404);
        $this->s->assign('u', $u);
        switch ($action) {
            /**
             * Topics created by the user
             * 
             */
            case 'topic':
                $count_topic = $this->post->query_post("user_id={$slug}&user_type=user_name&count=true");
                $this->dpagination->items($count_topic);
                $this->dpagination->limit($limit);
                $this->dpagination->currentPage($this->page);
                $this->dpagination->target('/member/' . $slug . '/topic');
                $this->dpagination->adjacents(8);
                $this->s->assign(
                        array(
                            'title' => $slug . '的帖子',
                            'post' => $this->post->query_post('user_id=' . $slug . '&user_type=user_name&page=' . $this->page . 'no=' . $limit),
                            'page_bar' => $this->dpagination->getOutput()
                        )
                );
                $this->s->display('user_topic.html');
                break;

            /**
             * fav topic of the user
             */
            case 'favtopic':
                $this->load->model('follow');
                $count_topic = $this->follow->count_fav($slug, 1);
                $this->dpagination->items($count_topic);
                $this->dpagination->limit($limit);
                $this->dpagination->currentPage($this->page);
                $this->dpagination->target('/member/' . $slug . '/favtopic');
                $this->dpagination->adjacents(8);
                $this->s->assign(
                        array(
                            'title' => $slug . '收藏的帖子',
                            'post' => $this->follow->list_follow($slug, 'user_name', 1, $this->page, $limit),
                            'page_bar' => $this->dpagination->getOutput()
                        )
                );
                $this->s->display('user_favtopic.html');
                break;

            case 'favnode':          
                $this->load->model('user');
                $this->load->model('nodes');
                $node = $this->user->get_user_fav_node($user['user_id']);
                $post = $this->nodes->get_user_fav_node_post($user['user_id'], $this->page, $limit, false);
                $count_post = $this->nodes->get_user_fav_node_post($user['user_id'], 0, 0, true);
                $this->dpagination->items($count_post);
                $this->dpagination->limit($limit);
                $this->dpagination->currentPage($this->page);
                $this->dpagination->target('/member/' . $slug . '/favnode');
                $this->dpagination->adjacents(8);

                $this->s->assign('title', '收藏的节点');
                $this->s->assign('node', $node);
                $this->s->assign('post', $post);
                $this->s->assign('page_bar', $this->dpagination->getOutput());
                $this->s->assign('show_page_bar', $count_post > 0);
                $this->s->display('user_favnode.html');
                break;

            case 'following':
                $this->load->model('user');
                $this->load->model('follow');
                $fo = $this->user->get_user_following_member($user['user_id']);
                $stream = $this->follow->get_following_user_stream($user['user_id'], false, $this->page, $limit);
                $count_post = $this->follow->get_following_user_stream($user['user_id'], true);
                $this->dpagination->items($count_post);
                $this->dpagination->limit($limit);
                $this->dpagination->currentPage($this->page);
                $this->dpagination->target('/member/' . $slug . '/following');
                $this->dpagination->adjacents(8);
                $if_show_page_bar = ($count_post > 0) ? TRUE : FALSE;
                $this->s->assign('title', '关注的用户');
                $this->s->assign('post', $stream);
                $this->s->assign('fo', $fo);
                $this->s->assign('show', $if_show_page_bar);
                $this->s->assign('page_bar', $this->dpagination->getOutput());
                $this->s->display('user_following.html');
                break;

            case 'send_message':
                $this->s->assign('title', '发送私信');
                $this->s->display('user_send_message.html');
                break;


            case 'blog':
                $blog = $this->post->query_post("user_id={$user['user_id']}&node_id=blog&node_type=node_name&no={$limit}");
                $count_blog = $this->post->query_post("user_id={$user['user_id']}&node_id=blog&node_type=node_name&count=" . TRUE);
                $if_show_page_bar = ($count_blog > $limit) ? TRUE : FALSE;
                $this->load->library('dpagination');
                $this->dpagination->items($count_blog);
                $this->dpagination->limit($limit);
                $this->dpagination->currentPage($page);
                $this->dpagination->target('/member/' . $slug . '/blog');
                $this->dpagination->adjacents(8);
                $this->s->assign('title', $u['user_name'] . '的blog');
                $this->s->assign('blog', $blog);
                $this->s->assign('show', $if_show_page_bar);
                $this->s->assign('page_bar', $this->dpagination->getOutput());
                $this->s->display('user_blog.html');
                break;

            case 'replies':
                $this->load->model('follow');
                $count_topic = $this->post->list_user_comment_post($slug, 'user_name', $page = 1, $limit, TRUE);
                $this->dpagination->items($count_topic);
                $this->dpagination->limit($limit);
                $this->dpagination->currentPage($this->page);
                $this->dpagination->target('/member/' . $slug . '/replies');
                $this->dpagination->adjacents(8);
                $this->s->assign(
                        array(
                            'title' => $slug . '回复的帖子',
                            'post' => $this->post->list_user_comment_post($slug, 'user_name', $this->page, $limit),
                            'page_bar' => $this->dpagination->getOutput()
                        )
                );
                $this->s->display('user_replies.html');

                break;

            /**
             * member info
             */
            default:
                $this->load->library('github');
                $this->load->model(array('post','follow'));
                $is_follow = $this->follow->check_follow($user['user_id'], $u['user_name'], 'f_keyname', $type = 3);
                $filed = (is_numeric($slug)) ? 'user_id' : 'user_name';
                $this->s->assign(array(
                    'title' => $u['user_name'],
                    'latest_blog' => '',
                    'github' => ($u['other']['github']) ? $this->github->fetch($u['other']['github']) : '',
                    'post'=>$this->post->query_post("user_id={$slug}&user_type={$field}&order=post_last_comment&page={$this->page}&no={$limit}"),
                    'hiscomment' => $this->post->list_user_comment_post($slug, $filed,  1, $limit),
                    'is_follow' => $is_follow
                ));
                $this->s->display('member.html');
                break;
        }
    }

}
/* End of file member.php */
/* Location: ./application/controllers/member.php */