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
 * @author	                  airyland <i@mao.li>
 * @copyright	              Copyright (c) 2012, mao.li.
 * @license	                  MIT
 * @link	                  https://github.com/airyland/nodeprint
 * @version	0.0.5
 */

/**
 * Topic pages
 * @author airyland <i@mao.li>
 */
class T extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('configs');
        $this->load->library('s');
    }

    function index() {
        redirect('/t/recent');
    }

    function the_post($id) {
        $page = $this->input->get_page();
        if(!$page){
            show_error('帖子不存在或者被删除', 404);
        }
        $this->load->model('post');
        $this->load->model('comment');
        $local_upload = $this->configs->item('local_upload');
        $comment_no = $this->configs->item('comment_no');
 
        $order = $this->input->get('order') ? $this->input->get('order') : 'ASC';
        $count=$this->comment->list_comment($id, 0, 'cm_id', $order, $page, $comment_no,TRUE);
        
        $this->load->library('dpagination');
        $this->dpagination->target("/t/{$id}");
        $this->dpagination->adjacents(8);
        $this->dpagination->items($count);
        $this->dpagination->limit($comment_no);
        $this->dpagination->currentPage($page);
        $page_bar = $this->dpagination->getOutput();
                
        $user = get_user();
        $this->post->get_post_fav_no($id);
        $this->post->add_post_hit($id);
        $topic = $this->post->post_info($id);
        if (!$topic)
            show_error('帖子不存在或者被删除', 404);
        $this->s->assign(array(
            'title' => $topic['post_title'],
            't' => $topic,
            'cm' => $this->comment->list_comment($id, 0, 'cm_id', $order, $page, $comment_no),
            'fav' => $this->post->check_post_fav($user['user_id'], $id),
            'local_upload'=>$local_upload,
            'page_bar'=>$page_bar
                )
        );

        $this->s->display('single_topic.html');
    }

    function the_list($cat, $key = '') {
        $page = $this->input->get('page');
        $limit = $this->configs->get_config_item('topic_no');
        if ($page == 0)
            $page = 1;
        if (!is_numeric($page) || !in_array($cat, array(' ', 'recent', 'changes', 'search')))
            show_error('抱歉，页面不存在', 404);
        $this->load->library('s');
        $this->load->model('post');
        $this->load->library('dpagination');


        switch ($cat) {
            /**
             * Recent added topics
             * @package topic
             */
            case 'recent':
                $post= $this->post->query_post("page={$page}&no={$limit}");
                $count_post=$this->post->query_post("count=true");
                $title = '最新主题';
                $this->dpagination->target('/t/recent');
                $template = 'topic_recent.html';
                break;
            /**
             * Recent changed topics
             * @package topic
             */
            case 'changes':
                $post=$this->post->query_list("orderby=post_last_comment&page={$page}&no={$limit}");
                $count_post=$this->post->query_list("count=true");
                $title = '最新更改';
                $this->dpagination->target('/t/changes');
                $template = 'topic_recent.html';
                break;
            /**
             * Search results
             * @package topic
             */
            case 'search':
                $key = urldecode($key);
                $count_post = $this->post->search_post($key, 0, 0, TRUE);
                $this->dpagination->target('/t/search/' . $key);
                $post = $this->post->search_post($key, $page, $limit);
                $title = $key . '-帖子搜索';
                $template = 'search_result.html';
                break;
        }

        $this->dpagination->adjacents(8);
        $this->dpagination->items($count_post);
        $this->dpagination->limit($limit);
        $this->dpagination->currentPage($page);
        $page_bar = $this->dpagination->getOutput();

        $this->s->assign(array(
            'title' => $title . ' ' . $page . '/' . intval($count_post / $limit + 1),
            'key' => $key,
            'post' => $post,
            'page_bar' => $page_bar,
            'count' => $count_post,
            'show_pagebar' => $count_post > 0 ? TRUE : FALSE
                )
        );
        $this->s->display($template);
    }

}

/* End of file t.php */
/* Location: ./application/controllers/t.php */
