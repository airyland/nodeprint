<?php
!defined('BASEPATH')&& ('No direct script asscess allowed');
include(APPPATH . 'libraries/smarty.php');
class S extends Smarty {
    protected $_ci;
    public function __construct() {
        parent::__construct();
        $this->template_dir = SMARTY_TEMPLATE_DIR;
        $this->compile_dir = SMARTY_COMPILE_DIR;
        $this->cache_dir = SMARTY_CACHE_DIR;
        $this->config_dir = SMARTY_CONFIG_DIR;
        $this->_ci = &get_instance();
        $this->_ci->load->model('user');
        $this->_ci->load->model('configs');
        global $lang;
        global $config;
        global $is_mobile;
        $current_user = get_user();
        //shoud set to FALSE on production env
        $this->compile_check=TRUE;
        //$this->caching=FALSE;
        $this->setCaching(0);

        function time_ago($paras) {
            return relative_time(strtotime($paras['time']));
        }

        $this->registerPlugin('function', 'time_ago', 'time_ago');
        $this->assign('is_login', is_login());
        $this->assign('is_admin', $this->_ci->auth->is_admin());
        $this->assign('site', $config);
        $this->assign('me', $this->_ci->user->get_user_profile($current_user['user_id'], 'user_id'));
        $this->assign('ga',$this->_ci->configs->item('ga'));
        $this->assign('lang', $lang);
        $this->assign('is_mobile',$is_mobile);
        $this->assign('_', $lang);
        //hook trigger::np_footer
        $this->assign('np_footer',$this->_ci->plugins->trigger('footer::np_footer'));
        $this->assign('np_version',NP_VERSION);
    }
}
