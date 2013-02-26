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


class Plugins
{

    /**
     * plugin dir
     *
     * @access private
     * @var string
     */
    public $plugins_dir = './application/plugins';


    /**
     * the registered listeners
     *
     * @access private
     * @var array
     */
    private $_listeners = array();

    /**
     * CI instance
     *
     * @access private
     * @var object
     */
    private $_CI;

    /**
     * construct
     * walk the active plugins and include them
     * @access public
     */
    public function __construct()
    {
        $this->_CI = & get_instance();
        $this->plugins_dir = FCPATH . NP_PLUGINS_DIR . DIRECTORY_SEPARATOR;
        $this->active_plugins = $this->get_active_plugin();
        if ($this->active_plugins && is_array($this->active_plugins)) {
            foreach ($this->active_plugins as $directory) {
                $plugin_dir = $directory . '/' . ucfirst($directory) . '.php';
                $path = FCPATH . NP_PLUGINS_DIR . '/' . $plugin_dir;
                if (preg_match("/^[\w\-\/]+\.php$/", $plugin_dir) && file_exists($path)) {
                    include_once($path);
                    log_message('debug', 'NodePrint:path ' . $path);
                    $class = ucfirst($directory);
                    if (class_exists($class)) {
                        /** initialize */
                        new $class($this);
                    }
                }
            }
        }
        log_message('debug', "NodePrint: Plugins Libraries Class Initialized");
    }

    /**
     * register hook
     *
     * @param string $hook
     * @param object $reference
     * @param string $method
     */
    public function register($hook, &$reference, $method)
    {
        $key = get_class($reference) . '->' . $method;
        $this->_listeners[$hook][$key] = array(&$reference, $method);
        log_message('debug', "$hook Registered: $key");
    }

    /**
     * trigger a hook
     *
     * @brief e.g.: $this->plugin->trigger('hook_name'[, arg1, arg2, arg3...]);
     * @param string $hook hook name
     * @return mixed
     */
    public function trigger($hook)
    {
        $result = '';
        if ($this->check_hook_exist($hook)) {
            foreach ($this->_listeners[$hook] as $listener) {
                $class = & $listener[0];
                $method = $listener[1];
                if (method_exists($class, $method)) {
                    $args = array_slice(func_get_args(), 1);
                    $result .= call_user_func_array(array($class, $method), $args);
                }
            }
        }
        log_message('debug', "Hook Triggered: $hook");
        return $result;
    }

    /**
     * check if hook exists
     *
     *
     * @param string $hook hook name
     * @return array
     */
    public function check_hook_exist($hook)
    {
        if (isset($this->_listeners[$hook]) && is_array($this->_listeners[$hook]) && count($this->_listeners[$hook]) > 0) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * active one plugin
     *
     * @param $name plugin directory
     */
    public function active_plugin($name)
    {
        if (in_array($name, $this->active_plugins)) {
            exit;
        }
        $this->active_plugins[] = $name;
        $this->save_active_plugin();
        redirect('/admin/plugins/');
    }

    /**
     * deactive one plugin
     *
     * @param $name plugin directory
     */
    public function deactive_plugin($name)
    {
        $key = array_search($name, $this->active_plugins);
        unset($this->active_plugins[$key]);
        $this->save_active_plugin();
        redirect('/admin/plugins/');
    }

    /**
     * save active plugin to database
     */
    public function save_active_plugin()
    {
        $this->_CI->db->update('config', array('value' => json_encode($this->active_plugins)), array('name' => 'plugin'));
    }

    /**
     * get active plugin from database
     * @return mixed
     */
    public function get_active_plugin()
    {
        $rs = $this->_CI->db->get_where('config', array('name' => 'plugin'));
        if ($rs->num_rows() > 0) {
            $json = $rs->row()->value;
            return json_decode($json, true);
        }
        return array();
    }

    /**
     * get one plugin info
     * @param $plugin directory name of the plugin
     * @return array
     */
    public function get($plugin)
    {
        $plugin = strtolower($plugin);
        $path = $this->plugins_dir . $plugin;
        $file = $path . DIRECTORY_SEPARATOR . ucfirst($plugin) . '.php';
        $config = $path . DIRECTORY_SEPARATOR . ucfirst($plugin) . '.config.php';
        if (!is_file($path) && file_exists($file)) {
            $fp = fopen($file, 'r');
            $plugin_data = fread($fp, 4096);
            fclose($fp);
            preg_match('|Plugin Name:(.*)$|mi', $plugin_data, $name);
            preg_match('|Plugin URI:(.*)$|mi', $plugin_data, $uri);
            preg_match('|Version:(.*)|i', $plugin_data, $version);
            preg_match('|Description:(.*)$|mi', $plugin_data, $description);
            preg_match('|Author:(.*)$|mi', $plugin_data, $author_name);
            preg_match('|Author Email:(.*)$|mi', $plugin_data, $author_email);
            foreach (array('name', 'uri', 'version', 'description', 'author_name', 'author_email') as $field) {
                ${$field} = (!empty(${$field})) ? trim(${$field}[1]) : '';
            }
            return array(
                'directory' => $plugin,
                'name' => ucfirst($name),
                'plugin_uri' => $uri,
                'description' => $description,
                'author' => $author_name,
                'author_email' => $author_email,
                'version' => $version,
                'configurable' => (file_exists($config)) ? TRUE : FALSE
            );
        }
        return;
    }

    /**
     * get all plugins info
     * @access public
     * @return array
     */
    public function get_all_plugins_info()
    {
        $data = array();
        $this->_CI->load->helper('directory');
        $plugin_dirs = directory_map($this->plugins_dir, TRUE);
        if ($plugin_dirs) {
            foreach ($plugin_dirs as $plugin_dir) {
                $data[] = $this->get($plugin_dir);
            }
        }
        return $data;
    }

    /**
     * get active plugin info
     *
     * @return array
     */
    public function get_active_plugin_info()
    {
        $data = array();
        $plugins = $this->get_active_plugin();
        foreach ($plugins as $plugin_dir) {
            $data[] = $this->get($plugin_dir);
        }
        return $data;
    }

    /**
     * get deactive plugin info
     *
     * @return array
     */
    public function get_deactive_plugin_info()
    {
        $deactive_plugins = array();
        $all_plugins = $this->get_all_plugins_info();
        $active_plugins = $this->get_active_plugin();
        foreach ($all_plugins as $plugin) {
            if (!in_array($plugin['directory'], $active_plugins)) {
                $deactive_plugins[] = $plugin;
            }
        }
        return $deactive_plugins;
    }
}


/* End of file Plugins.php */
/* Location: ./application/models/Plugins.php */