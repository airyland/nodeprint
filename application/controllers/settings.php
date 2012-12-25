<?php !defined('BASEPATH') && exit('No direct script access allowed');

class Settings extends CI_Controller {

    function index() {
        $this->load->helper('config');
        $lang = load_lang();
        $this->load->model('user');
        $this->load->library('s');
        global $user;
        $this->s->assign(array(
            'title' => $lang['settings'],
            'lang' => $lang,
            'avatar' => get_avatar($user['user_id'])
        ));

        $this->s->display('user_settings.html');
    }

}
/* End of file settings.php */
/* Location: ./application/controllers/settings.php */