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
     * User Topic feed
     * @param int $user_id
     */
    function user_feed($user_id) {
        $this->posts=$this->post->query_post("user_id={$user_id}");
        $this->generate_feed();
    }

    /**
     * Node Feed Generator
     * @param string $node_slug
     */
    function node_feed($node_slug) {
        $node=$this->db->get_where('node',array('node_slug'=>$node_slug))->row_array();
        $this->posts=$this->post->query_post("node_id={$node_slug}&node_type=node_slug");
        $this->s->assign('site_name',"{$node['node_name']}-Node Update-{$this->site_name}");
        $this->generate_feed();
    }

    /**
     * Comment Feed
     */
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