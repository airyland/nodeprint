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
        include(APPPATH.'language/'.$lang['lang'].'/admin_lang.php');
        $this->load->library('s');
        $this->load->model('configs');
        $config = $this->configs->get_config();
        $this->load->model('site');
        $status = $this->site->get_site_status();
        $admins = $this->admins->get_admin();
        $this->s->assign(array(
            's' => $status,
            'admins' => $admins,
            'title' => $lang['site settings'],
            'config' => $config,
            'last_backup_file' => $this->admins->get_last_backup_file()
        ));
        $this->s->display('admin/admin_index.html');
    }

    function the_action($action, $id = '') {
        global $lang;
        include(APPPATH.'language/'.$lang['lang'].'/admin_lang.php');
        $this->load->library('s');
        switch ($action) {
            /**
             * plugins management
             *
             */
            case 'plugins':
                if(!$id){
                    $plugins = $this->plugins->get_all_plugins_info();
                    $active_plugins=$this->plugins->get_active_plugin_info();
                    $deactivated_plugins =$this->plugins->get_deactive_plugin_info();
                    $this->s->assign(array(
                        'plugins'=>$plugins,
                        'd_plugins' => $deactivated_plugins,
                        'a_plugins'=>$active_plugins,
                        'title' => $lang['plugin management']
                    ));
                    $this->s->display('admin/admin_plugin.html');
                }else if($id==='active'){
                    $name=$this->input->get('name');
                    $this->plugins->active_plugin($name);
                }else if($id='deactive'){
                    $name=$this->input->get('name');
                    $this->plugins->deactive_plugin($name);
                }

                break;

            case 'settings':
                $this->s->assign(array(
                    'title' => $lang['site settings'],
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
                        'title' => $lang['node management'].'-'.$lang['site settings'],
                        'p_node' => $parent_node,
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
                        'title' => $lang['user management'].'-'.$lang['site settings'],
                        'p_node' => $parent_node,
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
                $this->dpagination->generate($this->post->query_post("count=TRUE"),20,$page,'/admin/topics/');

                if ($id === '') {
                    $node = $this->nodes->list_node(2, 0, 'node_id', 'DESC', 1, 15);
                    $parent_node = $this->nodes->list_node(1, 0, 'node_id', 'DESC', 1, 15);
                    $this->s->assign(array(
                        'title' => $lang["topic management"].'-'.$lang['site settings'] ,
                        'p_node' => $parent_node,
                        'title' => 'Dashboard',
                        'user' => $user,
                        'node' => $node,
                        'post' => $post,
                        'page_bar' => $this->dpagination->page_bar,
                        'single_page'=>$this->dpagination->is_single_page
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
                        'title' => $lang["page management"].'-'.$lang['site settings'],
                        'user' => get_user(),
                        'files' => $files,
                        'count' => count($files)
                    ));
                    $this->s->display('admin/admin_pages.html');
                } else {
                    $page = $id.'.html';
                    if (file_exists($directory . $page)) {
                        $this->s->assign(array(
                            'title' =>$lang["page management"],
                            'user' => get_user(),
                            'files' => $files,
                            'count' => count($files),
                            'name' => $id,
                            'file_content' => file_get_contents($directory . $page)
                        ));
                        $this->s->display('admin/admin_pages_edit.html');
                    } else {
                        show_error($lang['file does not exist'], 404);
                    }
                }
                break;

            /**
             * clear smarty cache
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

                    case 'manualBackup':
                        $this->load->model('admins');
                        $this->admins->manual_backup();
                        redirect('/admin/tools#manualBackup_success');
                    break;
                }
                break;

            case 'ga':
                $this->load->library('s');
                $this->s->display('admin/admin_ga.html');
                break;
        }
    }

}