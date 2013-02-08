<?php

!defined('BASEPATH') && exit('No direct script access allowed');

class Messages extends CI_Controller {
    public $page;
    public $no;
    private $is_ajax;
    /**
     * 构造器
     */
    function __construct() {
        parent::__construct();
        $this->auth->check_login();
        $this->load->model('message');
        $this->page = $this->input->get_page();
        $this->no=$this->input->get('no')?$this->input->get('no'):20;
        $this->is_ajax=$this->input->is_ajax_request();
    }

    /**
     * 消息页面，默认页为未读消息
     * @url /messages/
     */
    function index() {
        $type = $this->input->get('type');
        $read = ($type == 'unread' || !$type) ? 1 : -1;
        $reply_type = $type == 'sent' ? 'm_from_username' : 'm_to_username';
        $this->load->library('s');
        $this->load->library('dpagination');
        $user = get_user();
        $m_type = $type == 'pm' ? 4 : 0;
        $message = $this->message->list_message($user['user_name'], $reply_type, $read, $this->page, $this->no, 0, $m_type);
        $count_current_message = $this->message->list_message($user['user_name'], $reply_type, $read, $this->page, 20, 1, $m_type);
        $count_unread_message = $this->message->list_message($user['user_name'], 'm_to_username', 1, 1, 20, 1);
        $count_all_message = $this->message->list_message($user['user_name'], 'm_to_username', -1, 1, 20, 1);
        $count_pm_message = $this->message->list_message($user['user_name'], 'm_to_username', -1, 1, 20, 1, 4);

        $this->dpagination->items($count_current_message);
        $this->dpagination->limit($this->no);
        $this->dpagination->currentPage($this->page);
        $this->dpagination->target('/messages/?type=' . $type);
        $this->dpagination->adjacents(8);
        
        $this->s->assign(array(
            'title' => '我的消息',
            'message' => $message,
            'unread_count' => $count_unread_message,
            'all_count' => $count_all_message,
            'pm_count' => $count_pm_message,
            'pagination' => $this->dpagination->getOutput(),
            'has_msg' => $count_current_message>0,
            'is_dialog' => FALSE
        ));

        //set messages of current page read
        foreach($message as $m){
            $this->message->set_read($m['m_id'],'message');
        }

        if($this->is_ajax){
             //$this->s->assign('is_dialog',true);
             $this->s->display('message/message_main.html');
             return;
        }else{
            $this->s->display('message/message.html');
        }

        
    }

    /**
     * Send Message page
     * @url /messages/send
     */
    function send() {
        $to = $this->input->get('to') ? $this->input->get('to') : '';
        $this->load->library('s');
        $this->s->assign('title', '发送私信');
        $this->s->assign('to', $to);
        $this->s->display('user/user_send_message.html');
    }

}

/* End of file messages.php */
/* Location: ./application/controllers/messages.php */