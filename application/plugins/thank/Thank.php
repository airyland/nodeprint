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


/*
 *	Plugin Name: 主题感谢
 *	Plugin URI: http://mao.li/
 *	Description: 主题感谢
 *	Version: 0.1
 *	Author: airyland
 *	Author Email: i@mao.li
*/

class Thank
{
	private $_CI;

	public function __construct(&$plugin)
	{
		$plugin->register('topic_toolbar', $this, 'add_thank_to_topic');
		
		$this->_CI = &get_instance();
	}
	
	public function add_thank_to_topic($id)
	{
		
		return '<li><a href="/t/'.$id.'/thank">感谢</a></li>';
		
	}
}

/* End of file Thank.php */
/* Location: ./application/plugins/Thank.php */