<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/*
 *	Plugin Name: 日志归档列表Widget
 *	Plugin URI: http://www.cnsaturn.com/
 *	Description: 显示日志按月归档列表
 *	Version: 0.1
 *	Author: Saturn
 *	Author Email: huyanggang@gmail.com
*/

class Archive
{
	private $_CI;

	public function __construct(&$plugin)
	{
		$plugin->register('Widget::Categories', $this, 'show');
		
		$this->_CI = &get_instance();
	}

	/**
	*
	*
	*
	*/
	public function show()
	{
		return 'fuck2';
		
	}
}

/* End of file Archive.php */
/* Location: ./application/st_plugins/Archive.php */