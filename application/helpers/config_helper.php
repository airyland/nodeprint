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

function get_config_url(){
return $_SERVER['DOCUMENT_ROOT'].'/application/config/site_config.php';
}

function read_config($key=''){
include(get_config_url());
$return =($key=='')?$config:$config[$key];
return $return;
}

function write_config($data,$value=''){
include(get_config_url());
if(is_array($data)){
foreach($data as $key=>$value){
$config[$key]=$value;
}
}else{
$config[$data]=$value;
}
file_put_contents(get_config_url(),'<?php $config='.var_export($config,true).';');
}

?>