<?php !defined('BASEPATH') && exit('No direct script access allowed');

/**
 * Cron Controller
 * @author airyland <i@mao.li>
 * @version 0.5
 * @todo add cron support
 */
class Cron extends CI_Controller {

    
    function __construct(){
        parent::__construct();
        $this->load->model(array('configs','admins'));
    }
    
    function index() {
        show_404();
    }

    function backup() {
        $this->load->model('admins');
        $this->admins->auto_backup();
        header("HTTP/1.0 204 No Content");
    }

}