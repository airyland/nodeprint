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


class Settings extends CI_Controller {

    private $service_map = array(
        '' => '',
        'douban' => '豆瓣',
        'qq' => 'QQ',
        'github' => 'Github',
        'weibo' => '微博',
        'google' => 'Google'
        );

    protected $user_info;

    public function index() {
        global $lang;
        $this->auth->check_login();
        $this->load->model('user');
        $user = $this->auth->get_user();
        $this->user_info = $this->user->get_user_profile($user['user_id'],'user_id');
        $this->load->library('s');
        $this->s->assign(array(
            'title' => $lang['settings'],
            'lang' => $lang,
            'avatar' => get_avatar($user['user_id']),
            'gravatar' => 'http://www.gravatar.com/avatar/'.md5(strtolower(trim($this->user_info['user_email']))),
            'timestamp' => time(),
            'user_from' => $this->service_map[strtolower($this->user_info['user_from'])],
            'user_oauth' => $this->get_oauth_binding_list()
        ));
        $this->s->display('user/user_settings.html');
    }

    private function get_oauth_binding_list(){
        $rs = $this->db->get_where('oauth',array('user_id'=>$this->user_info['user_id']));
        return $rs->result_array();
    }

}
/* End of file settings.php */
/* Location: ./application/controllers/settings.php */
