<?php !defined('BASEPATH') && exit('No direct script access allowed');

/**
 * Feed Controller
 * @author airyland <i@mao.li>
 * @version 0.5
 */
class Feed extends CI_Controller {
    function __construct(){
        parent::__construct();
        $this->load->model('feeds');
    }

    function index() {
        $this->feeds->main_feed();
    }

    function post() {
        $this->feeds->main_feed();
    }

}
/* End of file post.php */
/* Location: ./application/controllers/feed */