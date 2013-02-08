<?php !defined('BASEPATH') && exit('No direct script access allowed');
/**
 * NodePrint
 *
 * 基于HTML5及CSS3的轻论坛程序
 *
 * NodePrint is an open source BBS System built on PHP and MySQL.
 *
 * @package    NodePrint
 * @author        airyland <i@mao.li>
 * @copyright    Copyright (c) 2012, mao.li.
 * @license        MIT
 * @link        https://github.com/airyland/nodeprint
 * @version    0.0.5
 */

/**
 * User Model
 *
 * 用户相关Model
 *
 * @package               NodePrint
 * @subpackage    Model
 * @category    user Model
 * @author        airyland <i@mao.li>
 * @link         http://github.com/airyland/nodeprint
 */
class User extends CI_Model
{
    const USER_TABLE = 'user';
    private $user_id;

    function __construct()
    {
        parent::__construct();
        global $config;
        $this->lang->load('operation', $config['lang']);
    }

    /**
     * get user_id by name
     *
     * @access public
     * @param string $user_name
     * @return int
     */
    function get_userid_by_name($user_name)
    {
        $this->db->cache_on();
        $rs = $this->db->select('user_id')->where('user_name', $user_name)->get(self::USER_TABLE);
        return $rs->num_rows() > 0 ? $rs->row()->user_id : 0;
    }

    /**
     * get user profile by user_id or user_name
     *
     * @access public
     * @param string $user_name
     * @param string $type
     * @return mixed
     */
    public function get_user_profile($user_name, $type = 'user_name')
    {
        $field = ($type == 'user_name') ? 'user_name' : 'user_id';
        $rs = $this->db->select('user_id,user_email,user_name,user_register_time,user_email_confirm,user_email_confirm_sent,user_profile_info,user_site_info')
            ->where($field, $user_name)
            ->get('vx_user');
        if ($rs->num_rows() > 0) {
            $profile = $rs->row_array();
            $profile['other'] = json_decode($profile['user_profile_info'], true);
            $profile['site'] = json_decode($profile['user_site_info'], true);
            unset($profile['user_profile_info']);
            unset($profile['user_site_info']);
            $profile['avatar'] = get_avatar($profile['user_id']);
        } else {
            $profile = 0;
        }
        return $profile;
    }

    /**
     * update user profile
     *
     * @access public
     * @todo 弃用
     */
    public function user_update_profile($user_id, $user_email, $github, $twitter, $douban, $weibo, $site, $location, $sign, $intro)
    {
        $data = array(
            'user_email' => $user_email,
            'user_profile_info' => json_encode(array(
                'github' => $github,
                'twitter' => $twitter,
                'weibo' => $weibo,
                'douban' => $douban,
                'site' => $site,
                'location' => $location,
                'sign' => $sign,
                'intro' => $intro
            ))
        );
        $this->db->set($data)->where('user_id', $user_id)->update(self::USER_TABLE);
    }

    /**
     *
     * @access public
     * @param int $user_id
     * @param string $oldpwd
     * @param string $newpwd
     * @param string $newpwd2
     * @return array
     */
    function user_update_password($user_id, $oldpwd, $newpwd, $newpwd2)
    {
        if ($newpwd != $newpwd2) {
            $e = 1;
            $msg = $this->lang->line('password not match');
            return array('error' => $e, 'msg' => $msg);
        }

        $user = $this->db->where('user_id', $user_id)->get(self::USER_TABLE);

        if ($user->num_rows() > 0) {
            $info = $user->row();
            $checkhash = md5(md5($oldpwd) . $info->user_salt);

            if ($info->user_pwd == $checkhash) {
                $newhash = md5(md5($newpwd) . $info->user_salt);
                $this->db->set('user_pwd', $newhash)->where('user_id', $user_id)
                    ->update('vx_user');
                $e = 0;
                $msg = $this->lang->line('password update success');

            } else {
                $e = 2;
                $msg = $this->lang->line('old password wrong');
            }
        } else {
            $e = 3;
            $msg = 'user does not exist';

        }
        return array('error' => $e, 'msg' => $msg);

    }

    /**
     * user register
     *
     * @access public
     * @param  string $user_name
     * @param  string $user_email
     * @param  string  $user_pwd
     * @return array
     */
    public function register_user($user_name, $user_email, $user_pwd)
    {
        $this->load->library('form_validation');
        $this->config->load('validation');

        if (!$this->form_validation->required($user_name) || !$this->form_validation->required($user_email) || !$this->form_validation->required($user_pwd)) {
            return array('error' => -1, 'msg' => '表单填写不完整');
        }

        if ($this->form_validation->numeric($user_name)) {
            return array('error' => 1, 'msg' => '账号不能为纯数字');
        }

        if (!$this->form_validation->min_length($user_name, $this->config->item('user_name_min_length')) || !$this->form_validation->max_length($user_name, $this->config->item('user_name_max_length'))) {
            return array('error' => 2, 'msg' => '账号字数不符合要求:' . $this->config->item('user_name_min_length'));
        }

        if (!$this->form_validation->valid_email($user_email)) {
            return array('error' => 3, 'msg' => '邮箱格式不正确');
        }

        if (!$this->form_validation->min_length($user_pwd, $this->config->item('user_pwd_min_length')) || !$this->form_validation->max_length($user_pwd, $this->config->item('user_pwd_max_length'))) {
            return array('error' => 4, 'msg' => '密码长度不符合要求');
        }

        if (!$this->form_validation->is_unique($user_name, 'vx_user.user_name')) {
            return array('error' => 5, 'msg' => '账号已存在');
        }

        if (!$this->form_validation->is_unique($user_email, 'vx_user.user_email')) {
            return array('error' => 6, 'msg' => '邮箱地址已被注册');
        }

        $this->load->helper('common');
        $salt = get_random_string();
        $user_email_confirm = get_random_string(16);
        $pwd = md5(md5($user_pwd) . $salt);
        $data = array(
            'user_name' => $user_name,
            'user_email' => $user_email,
            'user_email_confirm' => $user_email_confirm,
            'user_pwd' => $pwd,
            'user_salt' => $salt,
            'user_profile_info' => '',
            'user_register_time' => current_time(),
            'user_last_login' => current_time()
        );

        if ($this->db->insert('vx_user', $data)) {
            $this->_set_cookie($this->db->insert_id(), $user_name);
            return array('error' => 0, 'msg' => '注册成功');
        } else {
            return array('error' => 6, 'msg' => '未知错误');
        }
    }

    function _set_cookie($user_id, $user_name)
    {
        setcookie('NP_auth', authcode($user_id . "\t" . $user_name, 'ENCODE'), time() + 365 * 24 * 3600, '/');
    }

    function signin_by_uid($user_id){
        $user_name=$this->db->get_where('user',array('user_id'=>$user_id))->row()->user_name;
        $this->_set_cookie($user_id,$user_name);
        redirect();
    }

    function get_reset_pwd_key($email){
        return authcode($email . "\t" . strval(time()+ 2*24*3600), 'ENCODE');
    }

    function parse_reset_pwd_key($string){
        return explode("\t",authcode($string,'DECODE'));
    }
    /**
     * 用户登录
     *
     * @param string $user_name
     * @param string $user_pwd
     * @return array
     */
    public function login_user($user_name, $user_pwd)
    {
        $this->load->library('form_validation');
        $user_id = 0;
        $user_get_name = '';
        $msg = '';
        if (!$user_name || !$user_pwd) {
            $e = (!$user_name) ? -1 : -2;
            $msg = (!$user_name) ? '登录名为空' : '登录密码为空';
        } else {
            $this->load->helper('common');
            $is_email = $this->form_validation->valid_email($user_name);
            $field = $is_email ? 'user_email' : 'user_name';
            $check_user = $this->db->where($field, $user_name)->get('vx_user');
            if ($check_user->num_rows() > 0) {
                $rs = $check_user->row_array();
                $salt = $rs['user_salt'];
                $user_id = $rs['user_id'];
                $user_get_name = $rs['user_name'];
                $hash = md5(md5($user_pwd) . $salt);
                $check_pwd = $this->db->where(array($field => $user_name, 'user_pwd' => $hash))->get('vx_user');
                if ($check_pwd->num_rows() > 0) {
                    //更新最近登录时间
                    $this->db->update('vx_user', array('user_last_login' => current_time()), array('user_id' => $user_id));
                    $e = 0;
                    $msg = '登录成功';
                } else {
                    $e = 2;
                    $msg = '密码错误';
                }
            } else {
                $e = 1;
                $msg = $this->lang->line('user does not exist');
            }
        }
        return array($e, $msg, $user_id, $user_get_name);
    }

    function logout()
    {
        setcookie('NP_auth', '', time() - 8888, '/');
    }

    function user_list($order_by = 'user_id', $order = 'DESC', $page = 1, $no = 20)
    {
        $rs = $this->db->order_by($order_by, $order)->limit($no, count_offset($page, $no))->get('vx_user');
        return $rs->result_array();
    }

    /**
     * update user's site info including post count, comment count, fav count
     * @access public
     * @param int $user_id
     * @return void
     */
    function refresh_user_info($user_id = 0)
    {
        //fav topics
        $count_favtopic = $this->db->from('vx_follow')->where('f_type', 1)->where('user_id', $user_id)->count_all_results();
        $count_favnode = $this->db->from('vx_follow')->where('f_type', 2)->where('user_id', $user_id)->count_all_results();
        $count_following = $this->db->from('vx_follow')->where('f_type', 3)->where('user_id', $user_id)->count_all_results();
        $count_follower = $this->db->from('vx_follow')->where('f_type', 3)->where('f_keyid', $user_id)->count_all_results();
        $data = json_encode(array(
            'follower' => $count_follower,
            'following' => $count_following,
            'favtopic' => $count_favtopic,
            'favnode' => $count_favnode
        ));

        $this->db->set('user_site_info', $data)
            ->where('user_id', $user_id)
            ->update(self::USER_TABLE);
    }

    function bash_refresh_user_info()
    {
        $user = $this->db->select('user_id')->get(self::USER_TABLE)->result_array();
        foreach ($user as $u) {
            $this->refresh_user_info($u['user_id']);
        }
    }

    /**
     * email confirm
     * @access public
     * @param  string $auth
     * @return boolean
     */
    function email_confirm($auth)
    {
        $auth_check = $this->db->select('user_id')->where('user_email_confirm', $auth)
            ->get(self::USER_TABLE);
        if ($auth_check->num_rows() > 0) {
            $this->db->set('user_email_confirm', '1')->where('user_id', $auth_check->row()->user_id)
                ->update(self::USER_TABLE);
            return true;
        } else {
            return false;
        }
    }

    /**
     * get user fav node
     * @param string  $user
     * @param boolean $is_name
     * @return array|0
     */
    function get_user_fav_node($user, $is_name = false)
    {
        $rs = $this->db->select('*')
            ->from('vx_follow')
            ->where('f_type', 2)
            ->where('user_id', $user)
            ->join('vx_node', 'vx_node.node_id=vx_follow.f_keyid')
            ->get();
        return $rs->num_rows() > 0 ? $rs->result_array() : 0;
    }

    function get_user_following_member($user_id)
    {
        $rs = $this->db->where('user_id', $user_id)
            ->where('f_type', 3)
            ->get('vx_follow');
        return $rs->num_rows() > 0 ? $rs->result_array() : 0;
    }

    function do_send_email($user_id)
    {
        $this->db->update('vx_user', array('user_email_confirm_sent' => 1), array('user_id' => $user_id));
        // return $this->db->last_query();
    }

    /**
     * 计算在线用户
     */
    function count_online_user()
    {

    }

}

/* End of file user.php */
/* Location: ./application/models/user.php */