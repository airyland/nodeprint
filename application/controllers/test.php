<?php

!defined('BASEPATH') && exit('No direct script access allowed');

class Test extends CI_Controller {

    function index() {
        
    }

    function test1() {
        $this->load->model('post');
        $this->post->parse_param('node_id=1&node_type=name');
    }

    function md() {
        $this->load->model('pages');
        $md = "## hello world ";
        echo $this->pages->md2html($md);
    }

    function github() {
        //$this->load->library('github');
        //echo $this->github->fetch('airyland');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.github.com/users/' . 'airyland' . '/repos');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        $content = curl_exec($ch);
        curl_close($ch);
        if ($content) {
            echo $content;
        } else {
            echo 'nothing';
        }
        //log('debug', '获取github资料中');
        //echo  $content;
    }

}

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
