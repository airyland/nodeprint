<?php

/**
 * plugin Controllers
 * @autho airyland@qq.com <i@mao.li>
 */
!defined('BASEPATH') && exit('No direct script access allowed');
header("Content-type:text/html;charst=utf-8");

/**
 * Nodeprint Plugins Class
 *
 * 本类用于Plugins管理逻辑
 *
 * @package		Nodeprint
 * @subpackage	Controller
 * @category	Admin Controller
 * @author		airyland <i@mao.li>
 * @link 		https://github.com/airyland/nodeprint
 */
class Plugins extends CI_Controller {

    /**
     * 传递到对应视图的数据
     *
     * @access private
     * @var array
     */
    private $_data = array();

    /**
     * 解析函数
     *
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();

        /** privilege confirm */
        //$this->auth->exceed('administrator');
        //清除缓存
        $this->utility->clear_db_cache();

        $this->load->model('plugins_mdl');

        /** common data */
        $this->_data['parentPage'] = 'dashboard';
        $this->_data['currentPage'] = 'plugins';
        $this->_data['page_title'] = '插件管理';
    }

    /**
     * 默认执行函数
     *
     * @access public
     * @return void
     */
    public function index() {
        redirect('plugins/manage');
    }

    /**
     * 插件管理
     *
     * @access public
     * @return void
     */
    public function manage() {


        $plugins = $this->plugins_mdl->get_all_plugins_info();

        $activated_plugins = $this->utility->get_active_plugins();

        $deactivated_plugins = array();

        foreach ($plugins as $plugin) {
            if (!in_array($plugin, $activated_plugins)) {
                $deactivated_plugins[] = $plugin;
            }
        }

        $this->_data['activated_plugins'] = $activated_plugins;
        $this->_data['deactivated_plugins'] = $deactivated_plugins;

        print_r($this->_data);
        //$this->load->view('admin/plugins', $this->_data);
    }

    /**
     * 激活插件
     *
     * @access public
     * @param  string $name 插件目录名
     * @return void
     */
    public function activate($name) {
        $plugin = $this->plugins_mdl->get($name);
        $activated = 0;

        if ($plugin && is_array($plugin)) {
            $this->plugins_mdl->active($plugin);
            $activated++;
        }

        ($activated > 0) ? $this->_redirect_with_msg('success', '成功激活插件') : $this->_redirect_with_msg('error', '没有插件被激活');
    }

    /**
     * 禁用插件
     *
     * @access public
     * @param  string $name 插件目录名
     * @return void
     */
    public function deactivate($name) {
        $plugin = $this->plugins_mdl->get(strtolower($name));
        $deactivated = 0;

        if ($plugin && is_array($plugin)) {
            $this->plugins_mdl->deactive($plugin);
            $deactivated++;
        }

        ($deactivated > 0) ? $this->_redirect_with_msg('success', '成功禁用插件') : $this->_redirect_with_msg('error', '没有插件被禁用');
    }

    /** Todo: 插件管理(插件自定义参数设置) */
    public function config() {
        $config = $this->input->get('config');
        echo $config;
    }

    private function _redirect_with_msg($flag, $msg) {
        $this->session->set_flashdata($flag, $msg);

        go_back();
    }

}

/* End of file plugins.php */
/* Location: ./application/controllers/plugins.php */