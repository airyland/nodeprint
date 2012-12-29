<?php

!defined('BASEPATH') && exit('No direct script access allowed');

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
class Configs extends CI_Model {
    /**
     * 数据表名
     */
    const CONFIG_TABLE = 'vx_config';
    /**
     * 网站设置缓存目录
     */
    const CONFIG_CACHE_FILE = 'application/cache/site/config_cache.php';
    /**
     * 页脚统计代码页面
     */
    const CONFIG_FOOTER_FILE = 'application/templates/footer_analytics.html';
    /**
    * 不上首页节点
    */
    const NODE_HIDE_CACHE_FILE = 'application/cache/site/node_hide_cache.php';

    function __construct() {
        parent::__construct();
    }

    /**
     * save a config item
     * @param string $name
     * @param string $value
     * @return boolean 
     */
    function save_config_item($name, $value = '') {
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
    private function save_config_cache() {
        $rs = $this->db->get(self::CONFIG_TABLE)->result_array();
        $config = array();
        foreach ($rs as $conf) {
            if ($conf['name'] != 'analytics') {
                $config[$conf['name']] = $conf['value'];
                file_put_contents(self::CONFIG_CACHE_FILE, '<?php $config=' . var_export($config, true) . '; ?>');
            } else {
                file_put_contents(self::CONFIG_FOOTER_FILE, '<script>' . $conf['value'] . '</script>');
            }
        }
    }

    /**
     * 获得配置内容
     *
     * @access public
     * @return array
     */
    public function get_config() {
        if (!file_exists(self::CONFIG_CACHE_FILE))
            $this->save_config_cache();
        include(self::CONFIG_CACHE_FILE);
        return $config;
    }

    /**
     * 获得单个配置内容
     * 
     * @access public
     * @param string $item 配置key
     * @return string 
     */
    public function get_config_item($item) {
        $config = $this->get_config();
        return $config[$item];
    }

    public function item($item){
        return $this->get_config_item($item);
    }

/**
* 获取不上首页节点
*/
    public function get_hide_nodes(){
          if(!file_exists(self::NODE_HIDE_CACHE_FILE)){
            $this->load->model('nodes');
            $this->nodes->save_hide_nodes();
             }
             include(self::NODE_HIDE_CACHE_FILE);
             return $hide_nodes;

}

}

/* End of file configs.php */
/* Location: ./application/models/configs.php */