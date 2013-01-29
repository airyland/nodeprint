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
        $current_user = get_user();
        //模块编译改动检查，开发时请设为TRUE,线上时设为FALSE;
        $this->compile_check=TRUE;
        //是否开启缓存
        //$this->caching=FALSE;
        //缓存过期时间
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
        $this->assign('_', lang($config['lang']));
    }
}
