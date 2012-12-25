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
 * @license		GNU General Public License 2.0
 * @link		https://github.com/airyland/nodeprint
 * @version	0.0.5
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
     * 页面信息
     * @param string $slug
     */
    function get_page_info($slug){
       $pages= $this->db->where('page_slug',$slug)->get('vx_page');
        if($pages->num_rows>0) return $pages->row_array();
        return 0;
    }
    /**
     * 添加页面
     */
    function add_page($data){
        if(!$this->is_slug_exist($data['page_slug'])){
            $this->db->insert('vx_page',$data);
        }else{
            return 1;
        }
        
    }
    /*
     * slug是否存在
     */
    function is_slug_exist($slug){
        if($this->db->where('page_slug',$slug)->get('vx_page')->num_rows()>0){
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