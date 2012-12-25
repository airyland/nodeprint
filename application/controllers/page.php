<?php !defined('BASEPATH') && exit('No direct script access allowed');
/**
 * Page controller
 * @author airyland <i@mao.li>
 */
class Page extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('pages');
    }

    function index($slug = '') {
      if($slug){
          if($this->pages->get_page_info($slug)){
              $this->load->library('s');
              $this->s->display('page.html');
          }else{
              echo '页面不存在哦';
          }
          
      }else{
          echo '页面还不存在哦';
      }
    }    
}

/* End of file page.php */
/* Location: ./application/controllers/page.php */