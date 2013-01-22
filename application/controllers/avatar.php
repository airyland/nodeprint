<?php
!defined('BASEPATH') && exit('No direct script access allowed');
/**
 * Avatar handler
 *
 * @subpackage Controller
 * for easier user of avatar, we do not directly output the image, instead we can just user '/avatar/$Id/$size'as image's src, if user does not upload his own avatar, then output the default image '0.png';
 * the support sizes are 20, 48, 73.
 */
class Avatar extends CI_Controller
{

    public $imgdata;
    public $imgsrc;
    public $default='0.png';

    function __construct()
    {
        parent::__construct();
        header('Pragma: public');
        header('Cache-Control: max-age='.(86400*30));
        header('Expires: '. gmdate('D, d M Y H:i:s \G\M\T', time() + 86400*30));
        header('Content-Type: image/png');
    }

    function index($id, $size)
    {
        $size_map=array(
            '20'=>'s',
            '48'=>'m',
            '73'=>'l'
        );
        $this->imgsrc='img/avatar/'.$size_map[$size].'/'.$id.'.png';
        if(!$this->img_exists()){
            $this->imgsrc='img/avatar/'.$size_map[$size].'/'.$this->default;
        }

        $headers = apache_request_headers();
        $mtime=filemtime($this->imgsrc);
        if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) ==$mtime )) {
            // Client's cache IS current, so we just respond '304 Not Modified'.
            header('Last-Modified: '.gmdate('D, d M Y H:i:s', $mtime).' GMT', true, 304);
        } else {
            // Image not cached or cache outdated, we respond '200 OK' and output the image.
            header('Last-Modified: '.gmdate('D, d M Y H:i:s', $mtime).' GMT', true, 200);
            header('Content-Length: '.filesize($this->imgsrc));
            header('Content-Type: image/png');
           echo $this->img2data();
        }
    }

    public function by_name($name,$size){
        $user_id = $this->db->get_where('user',array('user_name'=>$name))->row()->user_id;
        $this->index($user_id,$size);
    }

    public function img2data()
    {
        return $this->imgdata = fread(fopen($this->imgsrc, 'rb'), filesize($this->imgsrc));
    }

    public function img_exists(){
        return file_exists($this->imgsrc);
    }
}

/* End of file avatar.php */
/* Location: ./application/controllers/avatar.php */