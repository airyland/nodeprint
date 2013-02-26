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
 * Page Model
 * @author airyland <i@mao.li>
 */
class Pages extends CI_Model {

    function __construct() {
        parent::__construct();
    }
    /**
     * get page info
     *
     * @param string $slug
     * @return mixed
     */
    function get_page_info($slug){
       $pages= $this->db->where('page_slug',$slug)->get('vx_page');
        if($pages->num_rows>0) return $pages->row_array();
        return 0;
    }
    /**
     * add page
     */
    function add_page($data){
        $data['post_time']=current_time();
        if(!$this->is_slug_exist($data['user_name'])){
            $this->db->insert('post',$data);
        }else{
            return 1;
        }
        
    }
    /*
     * check if slug duplicate
     */
    function is_slug_exist($slug){
        if($this->db->where('user_name',$slug)->get('post')->num_rows()>0){
            return TRUE;
        }
        return FALSE;
    }
    
    
    function md2html($string){
        include(APPPATH.'/libraries/Markdown.php');
        return  Markdown($string);
    }
        
}

/* End of file pages.php */
/* Location: ./application/models/pages.php */