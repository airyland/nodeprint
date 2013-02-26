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


class FetchAvatar
{
    /**
     * raw file save path
     * @var string
     */
    private $raw_path = 'np-content/upload/';
    /**
     * avatar file save path
     * @var string
     */
    private $save_path = 'img/avatar/';
    private $_ci;

    function __construct()
    {
    }

    /**
     * set file name
     * @param $path
     * @param $ext
     * @return bool|string
     */
    function set_filename($path, $ext)
    {
        mt_srand();
        $filename = md5(uniqid(mt_rand())) . '.' . $ext;
        if (!file_exists($path . $filename)) {
            return $filename;
        }
        $filename = str_replace($ext, '', $filename);
        $new_filename = '';
        for ($i = 1; $i < 100; $i++) {
            if (!file_exists($this->raw_path . $filename . $i . $ext)) {
                $new_filename = $filename . $i . $ext;
                break;
            }
        }

        if ($new_filename == '') {
            $this->set_error('upload_bad_filename');
            return FALSE;
        } else {
            return $new_filename;
        }
    }

    /**
     * do the fetch job
     *
     * @param string $url
     * @param int $size
     * @param bool $generate_all
     */
    function fetch($url, $size, $id, $generate_all = FALSE)
    {
        $type = array(
            'image/gif' => 'gif',
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png'
        );
        $header = get_headers($url, 1);
        if (is_array($header)) {
            $contentType = $header['Content-Type'];
        }
        $ch = curl_init($url);
        $name = $this->set_filename($this->raw_path, $type[$contentType]);
        $fp = fopen($this->raw_path . $name, "w");
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        if ($generate_all) {
            $this->save_avatar($name, $id, 20);
            $this->save_avatar($name, $id, 48);
            $this->save_avatar($name, $id, 73);
        } else {
            $this->save_avatar($name, $id, $size);
        }
    }

    /**
     * fetch Gravatar
     * @param $email
     * @param int $size
     */
    public function fetch_gravatar($size = 73,$user_id)
    {
        $this->_ci=&get_instance();
        $user_email=$this->_ci->db->get_where('user',array('user_id'=>$user_id))->row()->user_email;
        $this->fetch($this->get_gravatar_url($user_email, $size), $size, $user_id, TRUE);
    }

    /**
     * get Gravatar img url
     *
     * @param $email
     * @param int $s
     * @param string $d
     * @param string $r
     * @return string
     */
    public function get_gravatar_url($email, $s = 73, $d = 'mm', $r = 'g')
    {
        $url = 'http://www.gravatar.com/avatar/';
        $url .= md5(strtolower(trim($email)));
        $url .= "?s=$s&d=$d&r=$r";
        return $url;
    }

    /**
     * save avatar to specified user id
     *
     * @param string $img
     * @param int $id
     * @param int $size
     * @return bool
     */
    private function save_avatar($img, $id, $size)
    {
        $config = array(
            'image_library' => 'GD2',
            'source_image' => $this->raw_path . $img,
            'quality' => 100,
            'new_image' => $this->get_directory($size, $id),
            'width' => $size,
            'height' => $size
        );
        $_ci =& get_instance();
        $_ci->load->library('image_lib');
        $_ci->image_lib->clear();
        $_ci->image_lib->initialize($config);
        $do = $_ci->image_lib->resize();
        $_ci->image_lib->clear();
        return $do;
    }

    /**
     * get avatar directory
     * @param int $size
     * @param int $id
     * @return string
     */
    private function get_directory($size, $id)
    {
        $size_map = array(
            '20' => 's',
            '48' => 'm',
            '73' => 'l'
        );
        return $this->save_path . $size_map[$size] . '/' . $id . '.png';
    }
}

/* End of file FetchAvatar.php */
/* Location: ./application/libraries/FetchAvatar.php */
