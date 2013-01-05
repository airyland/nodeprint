<?php !defined('BASEPATH') && exit('No direct script access allowed');

/**
 * rss generater
 * @todo 添加帖子评论rss 添加用户帖子rss 网站帖子更新rss 网站最新帖子rss
 */
class Rss extends CI_Controller {

    function index() {
        header("Content-Type: application/xml; charset=utf-8");
        $this->load->driver('cache', array('adapter' => 'file'));
        $rss = $this->cache->get('rss');
        if (!$rss) {
            $this->load->model('post');
            $this->load->helper('rss');
            $this->load->helper('xml');
            $this->load->helper('config');
            $data = array(
                'title' => read_config('name') . '-rss',
                'link' => read_config('url'),
                'time' => current_time(),
                'description' => read_config('des'),
                'post'=>$this->post->query_post("")
            );
            $rss = generate_rss($data);
            $this->cache->save('rss', $rss, 120);
        }
        echo $rss;
    }
}

/* End of file rss.php */
/* Location: ./application/controllers/rss.php */
