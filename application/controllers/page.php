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
 * Page controller
 * @author airyland <i@mao.li>
 */
class Page extends CI_Controller {

    function __construct() {
        parent::__construct();
    }

    function index($slug = '') {
        if(!$slug) show_404();
        $content=$this->get_page_content($slug);
        if(!$content) show_404();
        $this->load->library('s');
        $this->s->assign(array(
                'title'=>$content['post_title'],
                'content'=>$content['post_content']
        ));
        $this->s->display('page/page.html');
    }

    /**
     * get page content
     *
     * @param  string $slug
     * @return minxed
     */
    function get_page_content($slug){
        $rs=$this->db->get_where('post',array('post_type'=>'page','user_name'=>$slug));
        if($rs->num_rows()>0){
            return $rs->row_array();
        }else{
            return false;
        }
    }
}

/* End of file page.php */
/* Location: ./application/controllers/page.php */