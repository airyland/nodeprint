<?php if (!defined('BASEPATH')) exit('No direct access allowed.');
/**
 * NodePrint
 *
 * 基于HTML5及CSS3的轻论坛程序
 * 
 * NodePrint is an open source BBS System built on PHP and MySQL.
 *
 * @package		NodePrint
 * @author		airyland <i@mao.li>
 * @copyright	Copyright (c) 2012, mao.li.
 * @license		GNU General Public License 2.0
 * @link		https://github.com/airyland/nodeprint
 * @version		0.0.5
 */
 
// ------------------------------------------------------------------------
 
/**
 * STBLOG Plugin Manager Class
 *
 * 插件经理类，用于管理STBlog的第三方插件，受到68KB的启发。
 *
 * @package		STBLOG
 * @subpackage	Libraries
 * @category	Libraries
 * @author		Saturn <huyanggang@gmail.com>
 * @link 		http://code.google.com/p/stblog/
 */
class Plugin
{
	/**
     * 已注册的插件(类和方法)
     *
     * @access private
     * @var array
     */
    private $_listeners = array();
	
	/**
    * CI句柄
    * 
    * @access private
    * @var object
    */
	private $_CI;

	 /**
     * 构造函数
     * 
     * @access public
     * @return void
     */
    public function __construct()
    {
        /** 获取CI句柄 **/
		$this->_CI = & get_instance();
		
		$plugins = $this->_CI->utility->get_active_plugins();
		
		if($plugins && is_array($plugins))
		{
			foreach($plugins as $plugin)
			{
				$plugin_dir = $plugin['directory'] . '/' . ucfirst($plugin['directory']) . '.php';
				
				$path = FCPATH . ST_PLUGINS_DIR . '/' . $plugin_dir;
				
				/** 仅能识别"插件目录/插件/插件.php"目录下的插件 */
				if (preg_match("/^[\w\-\/]+\.php$/", $plugin_dir) && file_exists($path))
				{
					include_once($path);

					$class = ucfirst($plugin['directory']);
					
					if (class_exists($class)) 
					{
						/** 初始化插件 */
						new $class($this);
					}
				}
			}
		}
		
		log_message('debug', "STBLOG: Plugins Libraries Class Initialized");
    }
	
	/**
	 * 注册需要监听的插件方法（钩子）
	 *
	 * @param string $hook
	 * @param object $reference
	 * @param string $method
	 */
	public function register($hook, &$reference, $method)
	{
		$key = get_class($reference).'->'.$method;
		$this->_listeners[$hook][$key] = array(&$reference, $method);
		
		log_message('debug', "$hook Registered: $key");
	}

	/**
	 * 触发一个钩子
	 *
	 *	e.g.: $this->plugin->trigger('hook_name'[, arg1, arg2, arg3...]);	
	 *
	 *
	 * @param string $hook 钩子的名称
	 * @param mixed $data 钩子的入参
	 * @return mixed
	 */
	public function trigger($hook)
	{
		$result = '';
		
		if($this->check_hook_exist($hook))
		{
			foreach ($this->_listeners[$hook] as $listener)
			{
				$class  = & $listener[0];
				$method = $listener[1];
				
				if(method_exists($class, $method))
				{
					$args = array_slice(func_get_args(), 1);
					
					$result .= call_user_func_array(array($class, $method), $args);
				}
			}
		}
		
		log_message('debug', "Hook Triggerred: $hook");
		
		return $result;
	}

	/**
	 * 检查钩子是否存在
	 *
	 *
	 * @param string $hook 钩子的名称
	 * @return array
	 */
	public function check_hook_exist($hook)
	{
		if(isset($this->_listeners[$hook]) && is_array($this->_listeners[$hook]) && count($this->_listeners[$hook]) > 0)
		{
			return TRUE;
		}
		
		return FALSE;
	}
}

/* End of file Plugin.php */
/* Location: ./application/libraries/Plugin.php */