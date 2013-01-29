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
class Site extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->driver('cache', array('adapter' => 'file'));
        $this->config->load('site');
    }

    /**
     * NodePrint's current version
     */

    const VERSION = '0.9';

    function server_info() {
        
    }

    /**
     * get current version of nodeprint
     * @return string
     */
    function get_version() {
        return self::VERSION;
    }

    function site_status() {
        $this->load->helper('date');
        $status = array();
        $status['user_no'] = $this->db->count_all('user');
        $status['post_no'] = $this->db->count_all('post');
        $status['comment_no'] = $this->db->count_all('comment');
        $status['node_no'] = $this->db->from('node')->where('node_type', 2)->count_all_results();
        $status['on_time'] = timespan(strtotime($this->config->item('site_start_date')));
        $status['hit_no'] = $this->db->select_sum('post_hit')->get('post')->row()->post_hit;
        return $status;
    }

    function save_status_cache() {
        
    }

    function get_site_status() {
        if (!$status = $this->cache->get('status')) {
            $status = $this->site_status();
            $this->cache->save('status', json_encode($status, 600));
        } else {
            $status = json_decode($status, TRUE);
        }
        return $status;
    }

}

/* End of file site.php */
/* Location: ./application/models/site.php */