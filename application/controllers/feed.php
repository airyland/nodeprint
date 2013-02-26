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