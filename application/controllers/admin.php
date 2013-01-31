<?php

!defined('BASEPATH') && exit('No direct script access allowed');

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
        global $lang;
        $this->load->library('s');
        $this->load->model('configs');
        $config = $this->configs->get_config();
        $this->load->model('site');
        $status = $this->site->get_site_status();
        $admins = $this->admins->get_admin();
        $this->s->assign(array(
            's' => $status,
            'admins' => $admins,
            'lang' => $lang,
            'title' => 'Dashboard',
            'config' => $config,
            'last_backup_file' => $this->admins->get_last_backup_file()
        ));
        $this->s->display('admin/admin_index.html');
    }

    function the_action($action, $id = '') {
        global $lang;
        $this->load->library('s');
        $this->s->assign('lang', $lang);
        switch ($action) {
            /**
             * 插件管理
             *
             */
            case 'plugin':
                //$this->utility->clear_db_cache();
                $this->load->model('plugins_mdl');
                $plugins = $this->plugins_mdl->get_all_plugins_info();
                $activated_plugins = $this->utility->get_active_plugins();
                $deactivated_plugins = array();
                //$this->load->model('metas_mdl');
                $this->load->library('plugin');
                echo $this->plugin->trigger('Widget::Categories', '<li><a href="{permalink}" title="{description}">{title} [{count}]</a></li>');
                echo 'fuck';
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

                $this->s->display('admin/admin_plugin.html');
                break;

            case 'settings':
                $this->s->assign(array(
                    'title' => '网站设置',
                ));
                $this->s->display('admin/admin_settings.html');
                break;
            /**
             * nodes management
             */
            case 'nodes':
                $this->load->model('nodes');
                $user = $this->user->user_list();
                if ($id == '') {
                    $node = $this->nodes->list_node(0, 0, 'node_id', 'DESC', 1, 0);
                    $parent_node = $this->nodes->list_node(1, 0, 'node_id', 'DESC', 1, 0);
                    $nodes = $this->nodes->get_all_nodes();
                    $this->s->assign(array(
                        'title' => '节点管理-网站设置',
                        'p_node' => $parent_node,
                        'lang' => $lang,
                        'user' => $user,
                        'node' => $node,
                        'nodes' => $nodes
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
                $page = $this->input->get('page');
                if (!$page) {
                    $page = 1;
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
                        'pagebar' => $this->dpagination->getOutput()
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
                $directory = $this->config->item('page_directory');
                $all_files = array_filter(directory_map($directory, 1), function($item) {
                            return strpos($item, 'html');
                        });
                $files = array();
                foreach ($all_files as $key => $val) {
                    $files[$key]['name'] = $val;
                    $files[$key]['time'] = filemtime($directory . $val);
                }
                unset($all_files);
                if ($id == '') {
                    $this->s->assign(array(
                        'title' => '页面管理',
                        'user' => get_user(),
                        'files' => $files,
                        'count' => count($files)
                    ));
                    $this->s->display('admin/admin_pages.html');
                } else {
                    $page = $this->input->get('p');
                    if (file_exists($directory . $page)) {
                        $this->s->assign(array(
                            'title' => '页面管理',
                            'user' => get_user(),
                            'files' => $files,
                            'count' => count($files),
                            'file_content' => file_get_contents($directory . $page)
                        ));

                        $this->s->display('admin/admin_pages_edit.html');
                    } else {
                        show_error('文件不存在哦', 404);
                    }
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
            case 'tools':
                $this->s->assign(array(
                    'title' => 'Admin tools'
                ));
                $this->s->display('admin/admin_tools.html');
                break;
            case 'tool':
                switch ($id) {
                    //clear smarty compiled templates
                    case 'clearCompiledTemplate':
                        $this->load->library('s');
                        $this->s->clearCompiledTemplate();
                        redirect('/admin/tools#clearCompiledTemplate_success');
                        break;

                    case 'clearAllCache':
                        $this->load->library('s');
                        $this->s->clearAllCache();
                        redirect('/admin/tools#clearAllCache_success');
                        break;
                }
                break;
        }
    }

}