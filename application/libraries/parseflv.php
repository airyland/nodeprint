<?php 
class Parseflv{

function _parse_youku($text){
if(preg_match("/http:\/\/v.youku.com\/v_show\/id_([^\/]+)(.html|)/i", $text, $matches)) {
			$flv = 'http://player.youku.com/player.php/sid/'.$matches[1].'/v.swf';
			/**
			if(!$width && !$height) {
				$api = 'http://v.youku.com/player/getPlayList/VideoIDS/'.$matches[1];
				$str = stripslashes(file_get_contents($api));
				if(!empty($str) && preg_match("/\"logo\":\"(.+?)\"/i", $str, $image)) {
					$url = substr($image[1], 0, strrpos($image[1], '/')+1);
					$filename = substr($image[1], strrpos($image[1], '/')+2);
					$imgurl = $url.'0'.$filename;
				}
			}
			**/
			return $flv;
		}
		
		
}

function _parse_tudou($text){
if(preg_match("/http:\/\/(www.)?tudou.com\/programs\/view\/([^\/]+)/i", $text, $matches)) {
			$flv = 'http://www.tudou.com/v/'.$matches[2];
			if(!$width && !$height) {
				$str = file_get_contents($url);
				if(!empty($str) && preg_match("/<span class=\"s_pic\">(.+?)<\/span>/i", $str, $image)) {
					$imgurl = trim($image[1]);
				}
			}
				return $flv;
		}
	
}

function parse($text){
//$text=$this::_parse_youku($text);
//$text=$this::_parse_tudou($text);
return $text;
}

}
?>