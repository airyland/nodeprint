<?php !defined('BASEPATH') && exit('No direct script access allowed');
/**
 * Page controller
 * @author airyland <i@mao.li>
 */
class Page extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('s');
    }

    function index($slug = '') {
        $path=APPPATH.'templates/page/'.$slug.'.html';
     if(file_exists($path)){
         $this->s->display("page/{$slug}.html");
     }else{
         show_404();
     }
    }    
}

/* End of file page.php */
/* Location: ./application/controllers/page.php */