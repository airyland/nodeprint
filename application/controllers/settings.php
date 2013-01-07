<?php !defined('BASEPATH') && exit('No direct script access allowed');

class Settings extends CI_Controller {

    function index() {
        $this->auth->check_login();
        $this->load->model('user');
        $user = $this->auth->get_user();
        $user_info = $this->user->get_user_profile($user['user_id'],'user_id');
        $lang =  load_lang();
        $this->load->library('s');
        $this->s->assign(array(
            'title' => $lang['settings'],
            'lang' => $lang,
            'avatar' => get_avatar($user['user_id']),
            'gravatar' => 'http://www.gravatar.com/avatar/'.md5(strtolower(trim($user_info['user_email']))),
            'timestamp' => time()
        ));
        $this->s->display('user_settings.html');
    }

}
/* End of file settings.php */
/* Location: ./application/controllers/settings.php */