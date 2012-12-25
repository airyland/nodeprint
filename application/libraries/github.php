<?php

/**
 * fetch github repositories
 * @author airyland <i@mao.li>
 */
class Github {
    protected $_CI;
    public $username;
    const base_url='https://api.github.com/users/';

    function __construct() {
        $this->_CI = & get_instance();
        $this->_CI->load->driver('cache', array('adapter' => 'file'));
    }

   private function _get_content($username) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/users/'.$username.'/repos');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        $content = curl_exec($ch);
        curl_close($ch);
        //log('debug', '获取github资料中');
        return $content;
    }

    public function fetch($username) {
       // $github = $this->_CI->cache->get('github' . $username);
       return $this->_get_content('airyland');
       // if (!$github) {
          //  $content = $this->_get_content($username);
            //if ($content === FALSE)
               // return false;
           // $this->_CI->cache->save('github' . $username, $content, 86400);
      //  }else {
        //    $content = $github;
       // }
       // return json_decode($content, TRUE);
    }

}

?>