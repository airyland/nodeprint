<?php
class MY_Input extends CI_Input
{

    function __construct()
    {
        parent::__construct();
    }

    function get($index = '', $xss_clean = TRUE)
    {
        return parent::get($index, $xss_clean);
    }

    function post($index = '', $xss_clean = TRUE)
    {
        return parent::post($index, $xss_clean);
    }

    function get_page()
    {
        if(!isset($_GET['page'])) return 1;
        if (!is_numeric( $_GET['page'])) {
            return 0;
        }else{
            return  $_GET['page'] && is_numeric( $_GET['page']) ? $_GET['page'] : 1;
        }
    }
}