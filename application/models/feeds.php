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
 * @license		GNU General Public License 2.0
 * @link		https://github.com/airyland/nodeprint
 * @version	0.0.5
 */
class Feeds extends CI_Model {

    public $site_name;
    public $site_url;
    public $site_descr;
    public $feed_post_no;
    public $feed_expire_time;
    public $site_email;

    function __construct() {
        parent::__construct();
        $this->load->model('post');
        $this->load->library('s');
        $this->load->model('configs');

        $this->site_name = $this->configs->item('name');
        $this->site_url = $this->configs->item('url');
        $this->site_description = $this->configs->item('description');

        $this->feed_post_no = $this->config->item('rss_items_no');
        $this->feed_expire_time = $this->config->item('feed_expire_time');

        $this->s->assign("site_name", $this->site_name);
        $this->s->assign("site_url", $this->site_url);
        $this->s->assign("site_description", $this->site_description);
        $this->s->assign('pubdate', current_time());
        $this->s->assign('lastbuiddate', current_time());
        $this->s->assign('site_email', 'i@mao.li');
    }

    function main_feed() {
        $this->posts = $this->post->query_post("");
        $this->generate_feed();
    }

    /**
     * user feed 
     * @param int $user_id
     */
    function user_feed($user_id) {
        $this->posts=$this->post->query_post("user_id={$user_id}");
        $this->generate_feed();
    }

    /**
     * node feed
     * @param int $node_id
     */
    function node_feed($node_slug) {
        $this->posts=$this->post->query_post("node_id={$node_slug}&node_type=node_slug");
        $this->generate_feed();
    }

    function comment_feed() {
        
    }

    function generate_feed() {
        header("Content-Type: application/xml; charset=utf-8");
        $this->s->assign('post', $this->posts);
        $this->s->display('rss.html');
    }

}

/* End of file feeds.php */
/* Location: ./application/models/feeds.php */