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

    function testTime(){
        // Make sure a default timezone is set... silly PHP 5.
        echo date('Y-m-d H:i:s',time());
        echo NP_START_TIME;
        echo date('Y-m-d H:i:s',NP_START_TIME);
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

    function testAjax(){
        $this->load->library('s');
        $this->s->display('test.html');
    }

    function json(){
        $a=array
       (
           "tid" => 467076,
           "dayslife" => 0,
           "data" => array
           (
               0 => array
               (
                   "date" => '2012-12-16',
                   "hour" => 20,
                   "minute" => 16,
                   "temperature" => 36.9,
                   "status.is_menses" => 0,
                   "status.is_together" => 0,
                   "test.test_ovulate" => 0,
                   "test.test_early" => 0,
                   "remarks" => ''
               ),

               1 => array
               (
                   "date" => '2012-12-17',
                   "hour" => 8,
                   "minute" => 10,
                   "temperature" => 38,
                   "status.is_menses" => 0,
                   "status.is_together" => 1,
                   "test.test_ovulate" => 0,
                   "test.test_early" => 0,
                   "remarks" => ''
               )



           )

       );
       // echo 'hello';
       // print_r($a);
       // echo json_encode($a);
        echo 'json_callback('.json_encode($a).')';
    }
    
    function testTime2(){
        //echo date('Y-m-d H:i:s',1358572242028);
        //$time= strtotime('2013-01-19 13:13:41');
        //echo $time;
        echo date('Y-m-d H:i:s',1358573670);
        echo strtotime('2013-01-19 13:16:11');
    }

}

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
