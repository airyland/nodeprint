<?php !defined('BASEPATH') && exit('No direct script access allowed');

/**
 * Cron Controller
 * @author airyland <i@mao.li>
 * @version 0.5
 * @todo add cron support
 */
class Cron extends CI_Controller {
    /**
     * 备份间隔
     * @var int
     */
    public $backup_time=1;
    function __construct(){
        parent::__construct();
        $this->load->model(array('configs','admins'));
    }
    
    function index() {
        
    }
/**
 * 备份数据
 * 
 * 备份方式为用js定时发送请求,备份由用户来触发，因为备份时间不定。
 */
    function backup() {
        $this->load->model('admins');
        $this->admins->backup();
    }

}