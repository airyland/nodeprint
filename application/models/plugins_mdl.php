<?php !defined('BASEPATH') && exit('No direct script access allowed');
/**
 * NodePrint
 *
 * 基于HTML5及CSS3的轻论坛程序
 * 
 * NodePrint is an open source BBS System built on PHP and MySQL.
 *
 * @package	NodePrint
 * @author		airyland <i@mao.li>
 * @copyright	Copyright (c) 2012, mao.li.
 * @license		GNU General Public License 2.0
 * @link		https://github.com/airyland/nodeprint
 * @version	0.0.5
 */
 
// ------------------------------------------------------------------------

/**
 * STBLOG Plugins Class
 *
 * �����������Model
 *
 * @package		STBLOG
 * @subpackage	Models
 * @category	Models
 * @author		Saturn <huyanggang@gmail.com>
 * @link		http://code.google.com/p/stblog/
 */
class Plugins_mdl extends CI_Model {
	
	/**
     * plugin dir
     * 
     * @access private
     * @var string
     */
	public $plugins_dir = '';
	
	/**
     *	active plugins
     *
     * @access public
     * @var string
     */
	public $active_plugins = array();
	
	/**
     * construct
     * 
     * @access public
     * @return void
     */
    public function __construct()
    {
       parent::__construct();
       
       
       $this->plugins_dir = FCPATH. ST_PLUGINS_DIR . DIRECTORY_SEPARATOR ;
       
       
       $this->active_plugins = $this->utility->get_active_plugins();
       
	   log_message('debug', "STBLOG: Plugins Model Class Initialized");
    }
    
	
	/**
	 * active plugin
	 *
     * @access public
	 * @param array $plugin 
	 * @return void
	 */
	public function active($plugin)
	{
		if (in_array($plugin, $this->active_plugins))
		{	
			return;
		} 
		else 
		{	
			$this->active_plugins[] = $plugin;
		}
		
		$active_plugins = serialize($this->active_plugins);
		
		$this->db->query("update settings set value='$active_plugins' where name='active_plugins'");
		
		$this->utility->clear_db_cache();
	}
	
	/**
	 * deactive plugin
	 *
     * @access public
	 * @param array $plugin 
	 * @return void
	 */
	public function deactive($plugin)
	{
		if (!in_array($plugin, $this->active_plugins))
		{
			return;
		} 
		else
		{
			$key = array_search($plugin, $this->active_plugins);
			
			unset($this->active_plugins[$key]);
		}
		
		$active_plugins = serialize($this->active_plugins);
		
		$this->db->query("update settings set value='$active_plugins' where name='active_plugins'");
		
		$this->utility->clear_db_cache();
	}

	/**
	 * get plugin
	 *
     * @access public
	 * @param array $name 
	 * @return array 
	 */
	public function get($plugin)
	{
		$plugin = strtolower($plugin);
		
		$path = $this->plugins_dir . $plugin;
				
		$file = $path . DIRECTORY_SEPARATOR . ucfirst($plugin) . '.php';
		
		$config = $path . DIRECTORY_SEPARATOR . ucfirst($plugin) . '.config.php';

		if(!is_file($path) && file_exists($file))
		{
			$fp = fopen($file, 'r' );
			
			
			$plugin_data = fread($fp, 4096);
			
			fclose($fp);
			
			preg_match( '|Plugin Name:(.*)$|mi', $plugin_data, $name );
			preg_match( '|Plugin URI:(.*)$|mi', $plugin_data, $uri );
			preg_match( '|Version:(.*)|i', $plugin_data, $version );
			preg_match( '|Description:(.*)$|mi', $plugin_data, $description );
			preg_match( '|Author:(.*)$|mi', $plugin_data, $author_name );
			preg_match( '|Author Email:(.*)$|mi', $plugin_data, $author_email );
			
			foreach( array('name', 'uri', 'version', 'description', 'author_name', 'author_email' ) as $field ) 
			{		
				${$field} = (!empty(${$field}))?trim(${$field}[1]):'';
			}
			
			return array(
						  'directory' => $plugin,
						  'name' => ucfirst($name), 
						  'plugin_uri' => $uri, 
						  'description' => $description, 
						  'author' => $author_name, 
						  'author_email' => $author_email, 
						  'version' => $version,
						  'configurable' => (file_exists($config))?TRUE:FALSE
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
				
		$this->load->helper('directory');
		
		$plugin_dirs = directory_map($this->plugins_dir, TRUE);
		
		if($plugin_dirs)
		{
			foreach($plugin_dirs as $plugin_dir)
			{
				$data[] = $this->get($plugin_dir);
			}
		}
		
		return $data;
	}
}

/* End of file plugins_mdl.php */
/* Location: ./application/models/plugins_mdl.php */