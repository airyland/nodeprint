<?php  !defined('BASEPATH') && exit('No direct script access allowed');
header("Content-type:text/html;charset='utf-8'");

/**
 * Admin Controller
 * @author airyland <i@mao.li>
 * @version 0.5
 */
class Admin extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('user');
        $this->load->model('admins');
        $this->auth->check_admin();
    }

    function index() {
        $this->load->library('s');
        $lang =load_lang();
        $this->load->model('configs');
        $config = $this->configs->get_config();
        $this->load->model('site');
        $status = $this->site->get_site_status();
        $admins=$this->admins->get_admin();
        $this->s->assign(array(
            's' => $status,
            'admins'=>$admins,
            'lang' => $lang,
            'title' => 'Dashboard',
            'config' => $config
        ));
        $this->s->display('admin/admin_index.html');
    }

    function the_action($action, $id = '') {
        $this->load->library('s');
        $lang = lang('en');
        $this->s->assign('lang', $lang);
        switch ($action) {
            /**
             * 插件管理
             *
             */
            case 'plugin':
                $this->utility->clear_db_cache();
                $this->load->model('plugins_mdl');
                $plugins = $this->plugins_mdl->get_all_plugins_info();
                $activated_plugins = $this->utility->get_active_plugins();
                $deactivated_plugins = array();
                $this->load->model('metas_mdl');

                //$this->plugin->trigger('Widget::Categories', '<li><a href="{permalink}" title="{description}">{title} [{count}]</a></li>');

                foreach ($plugins as $plugin) {
                    if (!in_array($plugin, $activated_plugins)) {
                        $deactivated_plugins[] = $plugin;
                    }
                }

                //$this->_data['activated_plugins'] = $activated_plugins;
                //$this->_data['deactivated_plugins'] = $deactivated_plugins;
                //print_r($this->_data);

                $this->s->assign(array(
                    'a_plugin' => $activated_plugins,
                    'd_plugin' => $deactivated_plugins,
                    'title' => '插件管理'
                ));

                $this->s->display('admin_plugin.html');
                break;

            case 'settings':
                $this->s->assign(array(
                    'title' => '网站设置',
                ));
                $this->s->display('admin/admin_settings.html');
                break;
            /**
             * 节点管理
             */
            case 'nodes':
                $this->load->model('nodes');
                $user = $this->user->user_list();
                if ($id == '') {
                    $node = $this->nodes->list_node(0, 0, 'node_id', 'DESC', 1, 15);
                    $parent_node = $this->nodes->list_node(1, 0, 'node_id', 'DESC', 1, 15);

                    $this->s->assign(array(
                        'title' => '节点管理-网站设置',
                        'p_node' => $parent_node,
                        'lang' => $lang,
                        'user' => $user,
                        'node' => $node
                    ));
                    $this->s->display('admin/admin_nodes.html');
                } else {
                    echo 'node edit';
                }
                break;

            case 'users':
                $this->load->model('nodes');
                $this->load->model('user');
                $user = $this->user->user_list();
                if ($id == '') {
                    $node = $this->nodes->list_node(2, 0, 'node_id', 'DESC', 1, 15);
                    $parent_node = $this->nodes->list_node(1, 0, 'node_id', 'DESC', 1, 15);
                    $this->s->assign(array(
                        'title' => '网站设置',
                        'p_node' => $parent_node,
                        'lang' => $lang,
                        'title' => 'Dashboard',
                        'user' => $user,
                        'node' => $node
                    ));
                    $this->s->display('admin/users.html');
                } else {
                    echo 'node edit';
                }
                break;

            case 'topics':
                $page=$this->input->get('page');
                if(!$page){
                    $page=1;
                }
                $this->load->library('dpagination');
                $this->load->model('nodes');
                $this->load->model('user');
                $this->load->model('post');
                $post = $this->post->query_post("page={$page}&no=20");
                $user = $this->user->user_list();

                $this->dpagination->items($this->post->query_post("count=TRUE"));
                $this->dpagination->limit(20);
                $this->dpagination->currentPage($page);
                $this->dpagination->target('/admin/topics/');
                $this->dpagination->adjacents(8);


                if ($id === '') {
                    $node = $this->nodes->list_node(2, 0, 'node_id', 'DESC', 1, 15);
                    $parent_node = $this->nodes->list_node(1, 0, 'node_id', 'DESC', 1, 15);
                    $this->s->assign(array(
                        'title' => '网站设置',
                        'p_node' => $parent_node,
                        'lang' => $lang,
                        'title' => 'Dashboard',
                        'user' => $user,
                        'node' => $node,
                        'post' => $post,
                        'pagebar'=>$this->dpagination->getOutput()
                    ));
                    $this->s->display('admin/topics.html');
                } else {
                    echo 'node edit';
                }
                break;

            /**
             * Page Management
             */
            case 'pages':
                      $this->load->helper('directory');
                      $this->config->load('site');
                      $directory=$this->config->item('page_directory');
                      $files=array_filter(directory_map($directory,1),function($item){return strpos($item,'html');});
                if ($id == '') {                 
                    $this->s->assign(array(
                        'title' => '页面管理',
                        'user' => get_user(),
                        'files'=>$files,
                        'count'=>count($files)
                    ));
                    $this->s->display('admin/admin_pages.html');
                } else {
                    echo 'page edit';
                }
                break;

                /**
                * 清除Smarty缓存
                */
                case 'clearCache':
                $this->load->library('s');
                $this->s->clearAllCache();
                break;
            
            case 'backup':
                $this->admins->backup();
                break;
        }
    }
}