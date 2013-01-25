<?php !defined('BASEPATH') && exit('No direct script access allowed');
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

    function server_info(){
        
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
        $status['user_no'] = $this->db->from('vx_user')->count_all_results();
        $status['post_no'] = $this->db->from('vx_post')->count_all_results();
        $status['comment_no'] = $this->db->from('vx_comment')->count_all_results();
        $status['node_no'] = $this->db->from('vx_node')->where('node_type', 2)->count_all_results();
        $status['on_time']= timespan(strtotime($this->config->item('site_start_date')));
        return $status;
    }

    function save_status_cache(){


    }

    function get_site_status(){
        if ( ! $status = $this->cache->get('status')){
            $status=$this->site_status();
            $this->cache->save('status',json_encode($status,600));
        }else{
            $status=json_decode($status,TRUE);
        }
        return $status;
    }

}

/* End of file site.php */
/* Location: ./application/models/site.php */