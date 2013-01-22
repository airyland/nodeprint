<?php

!defined('BASEPATH') && exit('No direct script access allowed');

/**
 * NodePrint
 *
 * 基于HTML5及CSS3的轻论坛程序
 *
 * NodePrint is an open source BBS System built on PHP and MySQL.
 *
 * @package    NodePrint
 * @author        airyland <i@mao.li>
 * @copyright    Copyright (c) 2012, mao.li.
 * @license        MIT
 * @link        https://github.com/airyland/nodeprint
 * @version    0.0.5
 */
class Configs extends CI_Model
{
    const CONFIG_TABLE = 'config';
    const CONFIG_CACHE_FILE = 'application/cache/site/config_cache.php';
    const CONFIG_FOOTER_FILE = 'application/templates/footer_analytics.html';

    function __construct()
    {
        parent::__construct();
    }

    /**
     * save a config item
     * @param string $name
     * @param string $value
     * @return boolean
     */
    function save_config_item($name, $value = '')
    {
        if ($value)
            $this->db->set('value', $value)
                ->where('name', $name)
                ->update(self::CONFIG_TABLE);

        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->db->set('value', $v)->where('name', $k)->update(self::CONFIG_TABLE);
            }
        }
        $this->save_config_cache();
        return ($this->db->affected_rows() > 0) ? TRUE : FALSE;
    }

    /**
     * 保存配置到缓存中
     *
     * @access private
     * @return void
     */
    private function save_config_cache()
    {
        $rs = $this->db->get(self::CONFIG_TABLE)->result_array();
        $config = array();
        foreach ($rs as $conf) {
            $config[$conf['name']] = $conf['value'];
            file_put_contents(self::CONFIG_CACHE_FILE, '<?php $config=' . var_export($config, true) . '; ?>');
        }
    }

    /**
     * 获得配置内容
     *
     * @access public
     * @return array
     */
    public function get_config()
    {
        $config = array();
        if (!file_exists(self::CONFIG_CACHE_FILE))
            $this->save_config_cache();
        include(self::CONFIG_CACHE_FILE);
        return $config;
    }

    /**
     * get single item
     *
     * @access public
     * @param string $item
     * @duplicate
     * @return string
     */
    public function get_config_item($item)
    {
        $config = $this->get_config();
        return $config[$item];
    }

    /**
     * get single item
     *
     * @param string $item
     * @return mixed
     */
    public function item($item)
    {
        return $this->get_config_item($item);
    }

}

/* End of file configs.php */
/* Location: ./application/models/configs.php */