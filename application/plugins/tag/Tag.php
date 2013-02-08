<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/*
 *	Plugin Name: 标签
 *	Plugin URI: http://mao.li/
 *	Description: 标签
 *	Version: 0.1
 *	Author: airyland
 *	Author Email: i@mao.li
*/

class Thank
{
	private $_CI;

	public function __construct(&$plugin)
	{
		$plugin->register('topic_info_footer', $this, 'show_tag');
		$plugin->register('');
		
		$this->_CI = &get_instance();
	}
	
	public function show_tags($id)
	{
		
		return '<li><a href="/t/'.$id.'/thank">感谢</a></li>';
		
	}

	private function get_tags(){

	}

	
}

/* End of file Thank.php */
/* Location: ./application/plugins/Thank.php */