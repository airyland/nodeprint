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
 * User Auth
 * 
 * @subpackage Model
 */
class Auth extends CI_Model {
    private $is_login;
    function __construct() {
        parent::__construct();
        $this->is_login=$this->is_login();
    }

    /**
     * check if user has signed in, otherwise redirect to signin page
     * 
     * @access public
     */
    public function check_login() {
        $user = $this->get_user();
        if ($user['error'] == 2) {
            if ($this->input->is_ajax_request()) {
                echo json_encode(array('error' => -1, 'msg' => 'not logined yet'));
                exit;
            } else {
                header("location:" . base_url() . 'signin?from=');
                exit();
            }
        }
    }
    
    function is_login(){
         if(isset($_SESSION['np_auth'])){
            return TRUE;
         }
         $user_status=$this->get_user();
         return $user_status['error']===1;
    }

    /**
     * get user info
     * 
     * @access public
     * @return array
     */
    public function get_user() {
        if(isset($_SESSION['np_auth'])){
            return array('error' => 1, 'user_id' => $_SESSION['np_auth']['user_id'], 'user_name' => $_SESSION['np_auth']['user_name']);
        }
        $e = 2;
        if (empty($_COOKIE['NP_auth'])) {
            $user_id = '';
            $user_name = '';
        } else {
            list($user_id, $user_name) = explode("\t", authcode($_COOKIE['NP_auth'], 'DECODE'));
            if ($user_id && $user_name) $e = 1;
            $_SESSION['np_auth'] = array('user_id' => $user_id, 'user_name' => $user_name);
        }
        return array('error' => $e, 'user_id' => $user_id, 'user_name' => $user_name);
    }

    /**
     * check if user has administrator access
     * 
     * @access public
     * @return boolean
     */
    public function is_admin() {
        $user = $this->get_user();
        if ($user['error'] === 1) {
            return $this->db->where('user_id', $user['user_id'])
                            ->where('user_flag', 9)
                            ->get('vx_user')
                            ->num_rows();
        }
        return FALSE;
    }

    /**
     * check if user has administrator access, otherwise redirect ot signin page
     * 
     * @access public
     */
    public function check_admin() {
        if (!$this->is_admin()) {
            redirect('/signin#no-admin-rights');
        }
    }

}

/* End of file auth.php */
/* Location: ./application/models/auth.php */