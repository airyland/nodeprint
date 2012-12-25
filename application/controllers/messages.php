<?php

!defined('BASEPATH') && exit('No direct script access allowed');

class Messages extends CI_Controller {
    /**
     * 构造器
     */
    function __construct() {
        parent::__construct();
        $this->auth->check_login();
        $this->load->model('message');
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
        $page = $this->input->get('page');
        if (!$page)
            $page = 1;
        $m_type = $type == 'pm' ? 4 : 0;
        $message = $this->message->list_message($user['user_name'], $reply_type, $read, $page, 10, 0, $m_type);
        $count_current_message = $this->message->list_message($user['user_name'], $reply_type, $read, $page, 20, 1, $m_type);
        $count_unread_message = $this->message->list_message($user['user_name'], 'm_to_username', 1, 1, 20, 1);
        $count_all_message = $this->message->list_message($user['user_name'], 'm_to_username', -1, 1, 20, 1);
        $count_pm_message = $this->message->list_message($user['user_name'], 'm_to_username', -1, 1, 20, 1, 4);

        $this->dpagination->items($count_current_message);
        $this->dpagination->limit(10);
        $this->dpagination->currentPage($page);
        $this->dpagination->target('/messages/?type=' . $type);
        $this->dpagination->adjacents(8);

        if ($type == 'unread' || !$type)
            $this->message->set_read(0, 'setallread');

        $this->s->assign(array(
            'title' => '我的消息',
            'message' => $message,
            'unread_count' => $count_unread_message,
            'all_count' => $count_all_message,
            'pm_count' => $count_pm_message,
            'pagination' => $this->dpagination->getOutput()
        ));
        $this->s->display('user_message.html');
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
        $this->s->display('user_send_message.html');
    }

}

/* End of file messages.php */
/* Location: ./application/controllers/messages.php */