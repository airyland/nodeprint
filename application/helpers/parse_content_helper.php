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


function parseflv($url,$width='',$height='') {
  $lowerurl = strtolower($url);
  $flv = '';
  if($lowerurl != str_replace(array('player.youku.com/player.php/sid/','tudou.com/v/','player.ku6.com/refer/'), '', $lowerurl)) {
    $flv = $url;
  } elseif(strpos($lowerurl, 'v.youku.com/v_show/') !== FALSE) {
    if(preg_match("/http:\/\/v.youku.com\/v_show\/id_([^\/]+)(.html|)/i", $url, $matches)) {
      $flv = 'http://player.youku.com/player.php/sid/'.$matches[1].'/v.swf';
    }
  } elseif(strpos($lowerurl, 'tudou.com/programs/view/') !== FALSE) {
    if(preg_match("/http:\/\/(www.)?tudou.com\/programs\/view\/([^\/]+)/i", $url, $matches)) {
      $flv = 'http://www.tudou.com/v/'.$matches[2];
      if(!$width && !$height) {
        $str = file_get_contents($url);
        if(!empty($str) && preg_match("/<span class=\"s_pic\">(.+?)<\/span>/i", $str, $image)) {
          $imgurl = trim($image[1]);
        }
      }
    }
  } elseif(strpos($lowerurl, 'v.ku6.com/show/') !== FALSE) {
    if(preg_match("/http:\/\/v.ku6.com\/show\/([^\/]+).html/i", $url, $matches)) {
      $flv = 'http://player.ku6.com/refer/'.$matches[1].'/v.swf';
    }
  } elseif(strpos($lowerurl, 'v.ku6.com/special/show_') !== FALSE) {
    if(preg_match("/http:\/\/v.ku6.com\/special\/show_\d+\/([^\/]+).html/i", $url, $matches)) {
      $flv = 'http://player.ku6.com/refer/'.$matches[1].'/v.swf';
    }
  } elseif(strpos($lowerurl, 'www.youtube.com/watch?') !== FALSE) {
    if(preg_match("/http:\/\/www.youtube.com\/watch\?v=([^\/&]+)&?/i", $url, $matches)) {
      $flv = 'http://www.youtube.com/v/'.$matches[1].'&hl=zh_CN&fs=1';
    }
  } elseif(strpos($lowerurl, 'tv.mofile.com/') !== FALSE) {
    if(preg_match("/http:\/\/tv.mofile.com\/([^\/]+)/i", $url, $matches)) {
      $flv = 'http://tv.mofile.com/cn/xplayer.swf?v='.$matches[1];
      if(!$width && !$height) {
        $str = file_get_contents($url);
        if(!empty($str) && preg_match("/thumbpath=\"(.+?)\";/i", $str, $image)) {
          $imgurl = trim($image[1]);
        }
      }
    }
  } elseif(strpos($lowerurl, 'v.mofile.com/show/') !== FALSE) {
    if(preg_match("/http:\/\/v.mofile.com\/show\/([^\/]+).shtml/i", $url, $matches)) {
      $flv = 'http://tv.mofile.com/cn/xplayer.swf?v='.$matches[1];
    }
  } elseif(strpos($lowerurl, 'you.video.sina.com.cn/b/') !== FALSE) {
    if(preg_match("/http:\/\/you.video.sina.com.cn\/b\/(\d+)-(\d+).html/i", $url, $matches)) {
      $flv = 'http://vhead.blog.sina.com.cn/player/outer_player.swf?vid='.$matches[1];
    }
  } elseif(strpos($lowerurl, 'http://v.blog.sohu.com/u/') !== FALSE) {
    if(preg_match("/http:\/\/v.blog.sohu.com\/u\/[^\/]+\/(\d+)/i", $url, $matches)) {
      $flv = 'http://v.blog.sohu.com/fo/v4/'.$matches[1];
    }
  } elseif(strpos($lowerurl, 'http://www.ouou.com/fun_funview') !== FALSE) {
    $str = file_get_contents($url);
    if(!empty($str) && preg_match("/var\sflv\s=\s'(.+?)';/i", $str, $matches)) {
      $flv = $_G['style']['imgdir'].'/flvplayer.swf?&autostart=true&file='.urlencode($matches[1]);
      if(!$width && !$height && preg_match("/var\simga=\s'(.+?)';/i", $str, $image)) {
        $imgurl = trim($image[1]);
      }
    }
  } elseif(strpos($lowerurl, 'http://www.56.com') !== FALSE) {

    if(preg_match("/http:\/\/www.56.com\/\S+\/play_album-aid-(\d+)_vid-(.+?).html/i", $url, $matches)) {
      $flv = 'http://player.56.com/v_'.$matches[2].'.swf';
      $matches[1] = $matches[2];
    } elseif(preg_match("/http:\/\/www.56.com\/\S+\/([^\/]+).html/i", $url, $matches)) {
      $flv = 'http://player.56.com/'.$matches[1].'.swf';
    }
  }
  if($flv) {
    if(!$width && !$height) {
      //return array('flv' => $flv, 'imgurl' => $imgurl);
      return $flv;
    } else {
      $width = addslashes($width);
      $height = addslashes($height);
      $flv = addslashes($flv);
      $randomid = 'flv_'.random(3);
      return '<span id="'.$randomid.'"></span><script type="text/javascript" reload="1">$(\''.$randomid.'\').innerHTML=AC_FL_RunContent(\'width\', \''.$width.'\', \'height\', \''.$height.'\', \'allowNetworking\', \'internal\', \'allowScriptAccess\', \'never\', \'src\', \''.$flv.'\', \'quality\', \'high\', \'bgcolor\', \'#ffffff\', \'wmode\', \'transparent\', \'allowfullscreen\', \'true\');</script>';
    }
  } else {
    return FALSE;
  }
}



//nl2br
function n2tobr($text){
    return str_replace(array("\r\n", "\r", "\n"), "</br>", $text); 
}

//auto imag 
function auto_image($text){
$pattern='/(htt[ps].*?(jpg|gif|png|jpeg))/';
$text=preg_replace($pattern,"<img src=\"$1\"/>",$text);
return $text;
}

//auto video
function auto_youku_video($text){
return preg_replace_callback("/http:\/\/v.youku.com\/v_show\/id_([^\/]+).html/i", '_youku_video',$text);
}

function _youku_video($matches){
    $page=$matches[0];
    $url='http://player.youku.com/player.php/sid/'.$matches[1].'/v.swf';
    return '<p class="video-info">视频地址：<a href="'.$page.'" rel="external">'.$page.'</a><p></br><embed src="'.$url.'" quality="high" width="638" height="420" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash"></embed></br>';
}


//auto link

function auto_links($text) {
  $pattern = "/(((http[s]?:\/\/)|(www\.))(([a-z][-a-z0-9]+\.)?[a-z][-a-z0-9]+\.[a-z]+(\.[a-z]{2,2})?)\/?[a-z0-9.,_\/~#&=;%+?-]+[a-z0-9\/#=?]{1,1})/is";
  //$pattern='/^(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?\.[a-zA-Z]{2,4}/';
  $text = preg_replace_callback($pattern,'_smart_link', $text);
  // fix URLs without protocols
  //$text = preg_replace("/href='www/", "href='http://www", $text);
  return $text;
}

function _smart_link($match){
$url=$match[1];
if(strpos($url,'v.youku')||strpos($url,'tudou')||strpos($url,'jpg')||strpos($url,'png')||strpos($url,'gif')) {
    if(parseflv($url)){
    $flv=parseflv($url);
    $e='<p class="video-info">视频地址：<a href="'.$url.'" rel="external">'.$url.'</a><p></br><embed src="'.$flv.'" quality="high" width="638" height="420" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash"></embed></br>'; 
    }else{
      $e=$url;
    }
    }else{
    if(!strpos($url,'jpg')){
        $e='<a href="'.$url.'">'.$url.'</a>';
    }

    if(strpos($url,'xiami')){
        $id=substr($url,strrpos($url,'/')+1);
        $e='<p class="music-info">歌曲地址：<a href="'.$url.'" rel="external">'.$url.'</a><p><embed src="http://www.xiami.com/widget/0_'.$id.'/singlePlayer.swf" type="application/x-shockwave-flash" width="257" height="33" wmode="transparent"></embed>';
    }

    }
    return $e;
}


//auto email
function auto_email($string) {
    return preg_replace_callback("/[_A-Za-z0-9-]+(\.[_A-Za-z0-9-]+)*(@)[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)*(\.[A-Za-z]{2,3})/", '_smart_email', $string);
}

function _smart_email($match){
    return '<span class="email">'.preg_replace('/@/','#',$match[0]).'</span>';
}


function parseemail($email='', $text) {
  $text = str_replace('\"', '"', $text);
  if(!$email && preg_match("/\s*([a-z0-9\-_.+]+)@([a-z0-9\-_]+[.][a-z0-9\-_.]+)\s*/i", $text, $matches)) {
    $email = trim($matches[0]);
    return '<a href="mailto:'.$email.'">'.$email.'</a>';
  } else {
    return '<a href="mailto:'.substr($email, 1).'">'.$text.'</a>';
  }
}

//get at users
function get_at_users($content){

$content= str_replace('@', ' @', $content) . '&nbsp;';

if (false !== strpos($content, '@'))
        {
             $content=str_replace('@',' @',$content).' ';
            if (preg_match_all('~\@([\w\d\_\-\x7f-\xff]+)(?:[\r\n\t\s ]+|[\xa1\xa1]+)~', $content, $match))
            {
                if (is_array($match[1]) && count($match[1]))
                {
return $match[1];
                }
            }
        }else{
            return 0;
        }
}

//auto user link

function auto_user_link($string){
    $string= str_replace('@', ' @', $string) . ' '.' ';
    $url=base_url().'member/';
    $string = preg_replace("/@(.*?)\s/", " @".'<a class="at-member" href="'.$url.'\\1">\\1</a> ', $string);
    return $string;
}


function parse_content($text){
$text=auto_links($text);
$text=auto_youku_video($text);
$text=auto_email($text);
$text=auto_user_link($text);
$text=auto_image($text);
$text=n2tobr($text);
return $text;
}
