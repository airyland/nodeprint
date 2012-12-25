<?php

function autolink($foo)
{
$foo = eregi_replace('(((f|ht){1}tp://)[-a-zA-Z0-9@:%_\+.~#?&//=]+)', '<a href="\1" target=_blank rel=nofollow>\1</a>', $foo);
if( strpos($foo, "http") === FALSE ){
$foo = eregi_replace('(www.[-a-zA-Z0-9@:%_\+.~#?&//=]+)', '<a href="http://\1" target=_blank rel=nofollow >\1</a>', $foo);
}else{
$foo = eregi_replace('([[:space:]()[{}])(www.[-a-zA-Z0-9@:%_\+.~#?&//=]+)', '\1<a href="http://\2" target=_blank rel=nofollow >\2</a>', $foo);
}
return $foo;
}

function formatUrlsInText($text){
$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
preg_match_all($reg_exUrl, $text, $matches);
$usedPatterns = array();
foreach($matches[0] as $pattern){
if(!array_key_exists($pattern, $usedPatterns)){
$usedPatterns[$pattern] = true;
//$text = str_replace($pattern, "<a href="{$pattern}" rel="nofollow">{$pattern}</a> ", $text);
}
}
return $text;
}


function txt2link($text){
// force http: on www.
$text = ereg_replace( "www\.", "http://www.", $text );
// eliminate duplicates after force
$text = ereg_replace( "http://http://www\.", "http://www.", $text );
$text = ereg_replace( "https://http://www\.", "https://www.", $text );  

// The Regular Expression filter
$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
// Check if there is a url in the text
if(preg_match($reg_exUrl, $text, $url)) {
// make the urls hyper links
$text = preg_replace($reg_exUrl, '<a href="'.$url[0].'" rel="nofollow">'.$url[0].'</a>', $text);
}    // if no urls in the text just return the text
return ($text);
}


function url_to_link($text) {
// The Regular Expression filter
$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
// Check if there is a url in the text
if (preg_match_all($reg_exUrl, $text, $url)) {
// make the urls hyper links
foreach($url[0] as $v){
//current position of the searached url
$curpos = strpos($text, ' '.$v)+1;
//delete the url
$text = substr_replace($text, '', $curpos, strlen($v));
//insert the link
$text = substr_replace($text, ''.$v.'', $curpos, 0);
}
return $text;
}
else {
// if no urls in the text just return the text
return $text;
}
}

//http://code.seebz.net/p/autolink-js/
//http://css-tricks.com/snippets/php/find-urls-in-text-make-links/
?>
